FROM node:20-alpine AS frontend
WORKDIR /app

ARG VITE_REVERB_APP_KEY=ab12cd34ef56gh78ij90
ARG VITE_REVERB_HOST=""
ARG VITE_REVERB_PORT=""
ARG VITE_REVERB_SCHEME=""
ARG VITE_APP_NAME=SkillUp

ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME \
    VITE_APP_NAME=$VITE_APP_NAME

COPY package*.json ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js jsconfig.json* ./
RUN npm run build

# ==========================================
# Stage 2: PHP Application & Nginx Server
# ==========================================
FROM php:8.3-fpm-alpine AS runtime

# Install System Dependencies & PHP Extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl \
    && apk del $PHPIZE_DEPS

# Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy Application Code (includes vendor)
COPY . .

# Remove any stale bootstrap cache and optimize autoloading
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN rm -f bootstrap/cache/*.php && \
    if [ -d "vendor" ]; then \
        composer dump-autoload --optimize --no-interaction; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Setup Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose web server and websocket ports
EXPOSE 80 8085

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

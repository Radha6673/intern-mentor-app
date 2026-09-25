#!/bin/sh
set -e

# Ensure permissions for storage and bootstrap/cache
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Nginx port dynamically (Render / Cloud platforms provide $PORT)
if [ -n "$PORT" ]; then
    sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf
    sed -i "s/listen \[::\]:80;/listen [::]:${PORT};/g" /etc/nginx/http.d/default.conf
fi

# Create storage symlink if not exists
php artisan storage:link --force || true

# Clear and optimize Laravel caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations if database connection is configured
if [ "$AUTO_MIGRATE" = "true" ] || [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# If arguments are passed, execute them (e.g. php artisan migrate)
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Default: Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

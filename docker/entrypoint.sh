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

# Create storage symlink if not exists
php artisan storage:link --force || true
php artisan config:clear || true

# If arguments are passed, execute them (e.g. php artisan migrate)
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

# Default: Start Supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

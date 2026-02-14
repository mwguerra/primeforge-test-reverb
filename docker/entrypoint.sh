#!/bin/sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database 2>/dev/null || true

DB_PATH="/var/www/html/database/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/app.conf

#!/bin/sh
set -e

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.docker ]; then
        cp /var/www/html/.env.docker /var/www/html/.env
    elif [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Install dependencies if vendor folder missing
if [ ! -d /var/www/html/vendor ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Ensure storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate Application Key if not set
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    php artisan key:generate --force
fi

exec "$@"

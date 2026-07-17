#!/bin/sh
set -e

cd /var/www/html

# Install PHP dependencies if missing
if [ ! -f "vendor/autoload.php" ]; then
    echo "Running composer install..."
    composer install --ignore-platform-reqs --no-dev --optimize-autoloader
fi

# Generate app key only if not already set
if [ -z "$APP_KEY" ]; then
    if grep -q '^APP_KEY=$' .env 2>/dev/null || ! grep -q '^APP_KEY=' .env 2>/dev/null; then
        php artisan key:generate --no-interaction
    fi
fi

# Link storage (gallery/testimonial image uploads live in storage/app/public)
php artisan storage:link || true

# Run migrations
echo "Waiting for database..."
for i in $(seq 1 30); do
    php artisan migrate --force && break
    echo "Attempt $i failed, retrying in 3s..."
    sleep 3
done

# Cache config for production (safe to ignore failures on first boot)
php artisan config:cache || true
php artisan route:cache || true

exec "$@"

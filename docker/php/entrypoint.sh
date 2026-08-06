#!/usr/bin/env bash
set -uo pipefail

cd /var/www/html

echo "[entrypoint] Nosso Hub PHP starting..."

if [ ! -f .env ]; then
    echo "[entrypoint] Creating .env from .env.example"
    cp .env.example .env || true
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/build

# Setup steps must not prevent php-fpm from starting
set +e

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
    composer dump-autoload --optimize
    php artisan package:discover --ansi
else
    echo "[entrypoint] vendor present — ensuring dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
fi

if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    echo "[entrypoint] Generating APP_KEY..."
    php artisan key:generate --force --ansi
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null

php artisan config:clear --ansi
php artisan migrate --force --ansi
php artisan storage:link --force --ansi

set -e

echo "[entrypoint] Starting: $*"
exec "$@"

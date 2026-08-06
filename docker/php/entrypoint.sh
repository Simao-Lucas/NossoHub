#!/usr/bin/env bash
set +e

cd /var/www/html || exit 1

echo "[entrypoint] Nosso Hub — PHP $(php -v | head -n1)"

if [ ! -f .env ]; then
    echo "[entrypoint] .env ausente — copiando .env.example"
    cp .env.example .env
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/build

chmod -R ug+rwx storage bootstrap/cache 2>/dev/null

wait_for_db() {
    local host="${DB_HOST:-mariadb}"
    local port="${DB_PORT:-3306}"
    local tries=60

    echo "[entrypoint] Aguardando MariaDB em ${host}:${port}..."
    for i in $(seq 1 "$tries"); do
        if php -r "try { new PDO('mysql:host=${host};port=${port}', getenv('DB_USERNAME') ?: 'nosso_hub', getenv('DB_PASSWORD') ?: 'secret'); exit(0);} catch (Throwable \$e) { exit(1);}" 2>/dev/null; then
            echo "[entrypoint] MariaDB OK"
            return 0
        fi
        sleep 2
    done
    echo "[entrypoint] AVISO: MariaDB não respondeu a tempo — migrate pode falhar"
    return 1
}

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
    composer dump-autoload --optimize 2>/dev/null
    php artisan package:discover --ansi 2>/dev/null
else
    echo "[entrypoint] vendor encontrado"
fi

if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    echo "[entrypoint] Gerando APP_KEY..."
    php artisan key:generate --force --ansi 2>/dev/null
fi

wait_for_db

php artisan config:clear --ansi 2>/dev/null
php artisan migrate --force --ansi 2>/dev/null
php artisan storage:link --force --ansi 2>/dev/null

echo "[entrypoint] Iniciando: $*"
exec "$@"

#!/usr/bin/env bash
set +e

cd /var/www/html || exit 1

COMPOSER_BIN="/usr/local/bin/composer"

echo "[entrypoint] PHP $(php -v | head -n1)"

if [ ! -f .env ]; then
    echo "[entrypoint] .env ausente — copiando .env.example"
    cp .env.example .env
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/tmp \
    storage/logs \
    bootstrap/cache \
    public/build

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null
chmod 1777 storage/framework/tmp 2>/dev/null

export TMPDIR=/var/www/html/storage/framework/tmp
export TEMP=/var/www/html/storage/framework/tmp
export TMP=/var/www/html/storage/framework/tmp

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
    if [ -x "$COMPOSER_BIN" ]; then
        "$COMPOSER_BIN" install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
        COMPOSER_EXIT=$?
        if [ "$COMPOSER_EXIT" -eq 0 ]; then
            "$COMPOSER_BIN" dump-autoload --optimize 2>/dev/null
            php artisan package:discover --ansi 2>/dev/null
        else
            echo "[entrypoint] ERRO: composer install falhou (exit ${COMPOSER_EXIT})"
        fi
    else
        echo "[entrypoint] ERRO FATAL: composer não encontrado em ${COMPOSER_BIN}"
    fi
else
    echo "[entrypoint] vendor encontrado"
fi

if [ -f vendor/autoload.php ]; then
    if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
        echo "[entrypoint] Gerando APP_KEY..."
        php artisan key:generate --force --ansi 2>/dev/null
    fi

    wait_for_db

    php artisan config:clear --ansi 2>/dev/null
    php artisan migrate --force --ansi 2>/dev/null
    php artisan storage:link --force --ansi 2>/dev/null

    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null
fi

php-fpm -t
if [ $? -ne 0 ]; then
    echo "[entrypoint] ERRO: configuração do php-fpm inválida"
    php-fpm -t
    exit 1
fi

echo "[entrypoint] Iniciando: $*"
exec "$@"

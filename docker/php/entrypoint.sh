#!/usr/bin/env bash
set +e

cd /var/www/html || exit 1

LOG_FILE="/var/www/html/debug-15b0e0.log"

# #region agent log
dbg() {
    local hyp="$1"
    local msg="$2"
    local data="${3:-{}}"
    local ts
    ts=$(date +%s%3N 2>/dev/null || date +%s000)
    printf '{"sessionId":"15b0e0","runId":"vendor-debug","hypothesisId":"%s","location":"entrypoint.sh","message":"%s","data":%s,"timestamp":%s}\n' \
        "$hyp" "$msg" "$data" "$ts" >> "$LOG_FILE" 2>/dev/null
    echo "[entrypoint][$hyp] $msg $data"
}
# #endregion

echo "[entrypoint] Nosso Hub — PHP $(php -v | head -n1)"

# #region agent log
dbg "A" "entrypoint_start" "{\"cwd\":\"$(pwd)\",\"php\":\"$(php -r 'echo PHP_VERSION;')\",\"composer\":\"$(command -v composer || echo missing)\",\"has_composer_json\":$([ -f composer.json ] && echo true || echo false),\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false),\"ls_root\":\"$(ls -1 | tr '\n' ' ' | sed 's/\"/\\\\\"/g')\"}"
# #endregion

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
    # #region agent log
    dbg "B" "composer_install_begin" "{\"reason\":\"vendor_missing\"}"
    # #endregion

    COMPOSER_OUTPUT=$(composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts 2>&1)
    COMPOSER_EXIT=$?

    # #region agent log
    COMPOSER_TAIL=$(echo "$COMPOSER_OUTPUT" | tail -n 20 | sed 's/"/\\"/g' | tr '\n' '|' | cut -c1-800)
    dbg "B" "composer_install_finished" "{\"exitCode\":${COMPOSER_EXIT},\"has_vendor_after\":$([ -f vendor/autoload.php ] && echo true || echo false),\"outputTail\":\"${COMPOSER_TAIL}\"}"
    # #endregion

    if [ "$COMPOSER_EXIT" -ne 0 ]; then
        # #region agent log
        dbg "C" "composer_failed" "{\"exitCode\":${COMPOSER_EXIT}}"
        # #endregion
        echo "[entrypoint] ERRO: composer install falhou (exit ${COMPOSER_EXIT})"
        echo "$COMPOSER_OUTPUT" | tail -n 40
    else
        composer dump-autoload --optimize 2>/dev/null
        php artisan package:discover --ansi 2>/dev/null
    fi
else
    # #region agent log
    dbg "A" "vendor_already_present" "{\"skip_composer\":true}"
    # #endregion
    echo "[entrypoint] vendor encontrado"
fi

# #region agent log
dbg "D" "pre_php_fpm_vendor_check" "{\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false),\"vendor_dir\":$([ -d vendor ] && echo true || echo false),\"writable\":$([ -w /var/www/html ] && echo true || echo false)}"
# #endregion

if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    echo "[entrypoint] Gerando APP_KEY..."
    php artisan key:generate --force --ansi 2>/dev/null
fi

wait_for_db

php artisan config:clear --ansi 2>/dev/null
php artisan migrate --force --ansi 2>/dev/null
php artisan storage:link --force --ansi 2>/dev/null

# #region agent log
dbg "E" "before_exec" "{\"cmd\":\"$*\",\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"
# #endregion

echo "[entrypoint] Iniciando: $*"
exec "$@"

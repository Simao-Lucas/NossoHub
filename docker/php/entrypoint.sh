#!/usr/bin/env bash
set +e

cd /var/www/html || exit 1

LOG_FILE="/var/www/html/debug-15b0e0.log"
COMPOSER_BIN="/usr/local/bin/composer"

# #region agent log
dbg() {
    local hyp="$1"
    local msg="$2"
    local data="${3:-{}}"
    local ts
    ts=$(date +%s%3N 2>/dev/null || date +%s000)
    printf '{"sessionId":"15b0e0","runId":"502-fix2","hypothesisId":"%s","location":"entrypoint.sh","message":"%s","data":%s,"timestamp":%s}\n' \
        "$hyp" "$msg" "$data" "$ts" >> "$LOG_FILE" 2>/dev/null
    echo "[entrypoint][$hyp] $msg $data"
}
# #endregion

echo "[entrypoint] Nosso Hub — PHP $(php -v | head -n1)"

# #region agent log
dbg "A" "entrypoint_start" "{\"php\":\"$(php -r 'echo PHP_VERSION;')\",\"composer_bin_exists\":$([ -x \"$COMPOSER_BIN\" ] && echo true || echo false),\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"
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
    dbg "B" "composer_install_begin" "{\"composerBin\":\"$COMPOSER_BIN\"}"
    # #endregion

    if [ ! -x "$COMPOSER_BIN" ]; then
        dbg "C" "composer_binary_missing" "{\"path\":\"$COMPOSER_BIN\"}"
        echo "[entrypoint] ERRO FATAL: composer não encontrado"
        COMPOSER_EXIT=127
        COMPOSER_OUTPUT="missing"
    else
        # Não capturar em variável gigante (pode estourar memória no homelab)
        "$COMPOSER_BIN" install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
        COMPOSER_EXIT=$?
        COMPOSER_OUTPUT="exit_only"
    fi

    # #region agent log
    dbg "B" "composer_install_finished" "{\"exitCode\":${COMPOSER_EXIT},\"has_vendor_after\":$([ -f vendor/autoload.php ] && echo true || echo false)}"
    # #endregion

    if [ "$COMPOSER_EXIT" -eq 0 ]; then
        "$COMPOSER_BIN" dump-autoload --optimize 2>/dev/null
        php artisan package:discover --ansi 2>/dev/null
    else
        dbg "C" "composer_failed" "{\"exitCode\":${COMPOSER_EXIT}}"
        echo "[entrypoint] ERRO: composer install falhou (exit ${COMPOSER_EXIT})"
    fi
else
    dbg "A" "vendor_already_present" "{}"
    echo "[entrypoint] vendor encontrado"
fi

dbg "D" "pre_php_fpm_vendor_check" "{\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"

if [ -f vendor/autoload.php ]; then
    if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
        echo "[entrypoint] Gerando APP_KEY..."
        php artisan key:generate --force --ansi 2>/dev/null
    fi

    wait_for_db

    php artisan config:clear --ansi 2>/dev/null
    php artisan migrate --force --ansi 2>/dev/null
    php artisan storage:link --force --ansi 2>/dev/null
else
    dbg "C" "skip_artisan_no_vendor" "{}"
    echo "[entrypoint] AVISO: vendor ausente — pulando artisan"
fi

# #region agent log
LISTEN_CFG=$(grep -hR "^listen" /usr/local/etc/php-fpm.d/ 2>/dev/null | tr '\n' ';' | sed 's/"/\\"/g')
FPM_TEST=$(php-fpm -t 2>&1 | tr '\n' '|' | sed 's/"/\\"/g' | cut -c1-500)
FPM_TEST_EXIT=$?
dbg "G" "fpm_listen_config" "{\"listenLines\":\"${LISTEN_CFG}\"}"
dbg "I" "fpm_config_test" "{\"exitCode\":${FPM_TEST_EXIT},\"output\":\"${FPM_TEST}\"}"
# #endregion

if [ "$FPM_TEST_EXIT" -ne 0 ]; then
    echo "[entrypoint] ERRO: php-fpm -t falhou:"
    php-fpm -t
    # Não sair em loop cego: manter container vivo para debug
    dbg "I" "fpm_test_failed_sleeping" "{}"
    sleep infinity
fi

dbg "E" "before_exec" "{\"cmd\":\"$*\",\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"
dbg "F" "php_fpm_starting" "{}"

echo "[entrypoint] Iniciando: $*"
exec "$@"

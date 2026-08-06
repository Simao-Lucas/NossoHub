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
    printf '{"sessionId":"15b0e0","runId":"tempnam-fix","hypothesisId":"%s","location":"entrypoint.sh","message":"%s","data":%s,"timestamp":%s}\n' \
        "$hyp" "$msg" "$data" "$ts" >> "$LOG_FILE" 2>/dev/null
    echo "[entrypoint][$hyp] $msg $data"
}
# #endregion

echo "[entrypoint] Nosso Hub — PHP $(php -v | head -n1)"

dbg "A" "entrypoint_start" "{\"php\":\"$(php -r 'echo PHP_VERSION;')\",\"composer_bin_exists\":$([ -x \"$COMPOSER_BIN\" ] && echo true || echo false),\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"

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

# php-fpm roda como www-data — storage precisa ser gravável por ele
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null
chmod 1777 storage/framework/tmp 2>/dev/null

export TMPDIR=/var/www/html/storage/framework/tmp
export TEMP=/var/www/html/storage/framework/tmp
export TMP=/var/www/html/storage/framework/tmp

# #region agent log
probe_write() {
    local dir="$1"
    local who="$2"
    if su -s /bin/sh "$who" -c "test -w '$dir' && touch '$dir/.write_probe' && rm -f '$dir/.write_probe'" 2>/dev/null; then
        echo "ok"
    else
        echo "fail"
    fi
}
WWW_UID=$(id -u www-data 2>/dev/null || echo "?")
HOST_LS=$(ls -ld storage storage/framework storage/framework/tmp bootstrap/cache 2>/dev/null | tr '\n' ';' | sed 's/"/\\"/g')
dbg "K" "storage_permissions" "{\"wwwUid\":\"$WWW_UID\",\"listing\":\"$HOST_LS\",\"www_storage\":\"$(probe_write storage www-data)\",\"www_tmp\":\"$(probe_write storage/framework/tmp www-data)\",\"www_views\":\"$(probe_write storage/framework/views www-data)\",\"www_cache\":\"$(probe_write storage/framework/cache/data www-data)\",\"www_bootstrap\":\"$(probe_write bootstrap/cache www-data)\",\"tmpdir\":\"$TMPDIR\"}"
TEMPNAM_TEST=$(php -r '$_tmp="/var/www/html/storage/framework/tmp"; $f=@tempnam($_tmp,"nh"); echo ($f===false?"fail":"ok:".$f); if($f){@unlink($f);}' 2>&1 | sed 's/"/\\"/g')
dbg "K" "tempnam_probe" "{\"result\":\"$TEMPNAM_TEST\"}"
# #endregion

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
    dbg "B" "composer_install_begin" "{\"composerBin\":\"$COMPOSER_BIN\"}"

    if [ ! -x "$COMPOSER_BIN" ]; then
        dbg "C" "composer_binary_missing" "{\"path\":\"$COMPOSER_BIN\"}"
        echo "[entrypoint] ERRO FATAL: composer não encontrado"
        COMPOSER_EXIT=127
    else
        "$COMPOSER_BIN" install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
        COMPOSER_EXIT=$?
    fi

    dbg "B" "composer_install_finished" "{\"exitCode\":${COMPOSER_EXIT},\"has_vendor_after\":$([ -f vendor/autoload.php ] && echo true || echo false)}"

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

    # Reaplica ownership após artisan (pode criar arquivos como root)
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
    chmod -R ug+rwx storage bootstrap/cache 2>/dev/null
else
    dbg "C" "skip_artisan_no_vendor" "{}"
    echo "[entrypoint] AVISO: vendor ausente — pulando artisan"
fi

FPM_TEST=$(php-fpm -t 2>&1 | tr '\n' '|' | sed 's/"/\\"/g' | cut -c1-500)
FPM_TEST_EXIT=$?
dbg "I" "fpm_config_test" "{\"exitCode\":${FPM_TEST_EXIT},\"output\":\"${FPM_TEST}\"}"

if [ "$FPM_TEST_EXIT" -ne 0 ]; then
    echo "[entrypoint] ERRO: php-fpm -t falhou:"
    php-fpm -t
    dbg "I" "fpm_test_failed_sleeping" "{}"
    sleep infinity
fi

dbg "E" "before_exec" "{\"cmd\":\"$*\",\"has_vendor_autoload\":$([ -f vendor/autoload.php ] && echo true || echo false)}"
dbg "F" "php_fpm_starting" "{}"

echo "[entrypoint] Iniciando: $*"
exec "$@"

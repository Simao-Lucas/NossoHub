#!/bin/sh
set -e

echo "Waiting for MariaDB at ${DB_HOST:-mariadb}:${DB_PORT:-3306}..."
i=0
while [ "$i" -lt 60 ]; do
  if python - <<'PY'
import os, sys
import pymysql
try:
    pymysql.connect(
        host=os.environ.get("DB_HOST", "mariadb"),
        port=int(os.environ.get("DB_PORT", "3306")),
        user=os.environ.get("DB_USERNAME", "nosso_hub"),
        password=os.environ.get("DB_PASSWORD", "secret"),
        database=os.environ.get("DB_DATABASE", "nosso_hub"),
        connect_timeout=2,
    ).close()
except Exception:
    sys.exit(1)
sys.exit(0)
PY
  then
    echo "MariaDB is ready."
    break
  fi
  i=$((i + 1))
  sleep 1
done

mkdir -p /app/media/events
exec "$@"

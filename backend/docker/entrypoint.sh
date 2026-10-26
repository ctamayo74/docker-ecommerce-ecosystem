#!/bin/sh
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY no definido. Genera uno con: php artisan key:generate --show" >&2
  exit 1
fi

# La config se cachea en runtime porque las variables llegan al arrancar el contenedor
su-exec www-data php artisan config:cache
su-exec www-data php artisan route:cache
su-exec www-data php artisan event:cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  su-exec www-data php artisan migrate --force
fi

exec "$@"

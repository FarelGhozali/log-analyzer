#!/bin/sh

set -eu

cd /var/www/html

mkdir -p database
touch database/database.sqlite

if [ -z "${APP_KEY:-}" ]; then
    if [ -s storage/app.key ]; then
        export APP_KEY="$(cat storage/app.key)"
    else
        umask 077
        export APP_KEY="$(php artisan key:generate --show --no-interaction)"
        printf '%s' "$APP_KEY" > storage/app.key
    fi
fi

php artisan migrate --force --no-interaction
php artisan optimize:clear --no-interaction
php artisan optimize --no-interaction
php artisan storage:link --force

exec "$@"

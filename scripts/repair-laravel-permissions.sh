#!/bin/sh

set -eu

APP_USER="${APP_USER:-sail}"
APP_GROUP="${APP_GROUP:-sail}"

echo "Repairing Laravel writable directories for ${APP_USER}:${APP_GROUP}..."

mkdir -p \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R "${APP_USER}:${APP_GROUP}" \
    storage/app/private \
    storage/framework \
    storage/logs \
    bootstrap/cache

chmod -R u+rwX,g+rwX \
    storage/app/private \
    storage/framework \
    storage/logs \
    bootstrap/cache

php artisan optimize:clear >/dev/null 2>&1 || true

echo "Laravel writable directories are ready."

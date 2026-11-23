#!/bin/sh
# Prepares a fresh checkout and starts the API, so `docker compose up` is all you need.
set -e

cd /app

# vendor/ lives in a named volume, which Docker fills from the image only once. Reinstall when it's
# empty or older than composer.lock (e.g. after pulling new dependencies).
if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/composer/installed.json ]; then
    composer install --no-interaction --prefer-dist --no-scripts
fi

# The source is bind-mounted, so .env is created on the host on first start.
if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# Rebuilt on every start: the vendor volume is filled from the image, not by composer scripts.
php artisan package:discover --ansi

php artisan migrate --force
php artisan demo:seed-if-empty

# /storage URLs for uploaded equipment photos.
if [ ! -e public/storage ]; then
    php artisan storage:link
fi

exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload

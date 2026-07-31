#!/usr/bin/env sh
set -eu

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan config:cache
php artisan view:cache

exec apache2-foreground

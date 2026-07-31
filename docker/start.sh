#!/usr/bin/env sh
set -eu

app_port="${PORT:-80}"
sed -ri "s/^Listen 80$/Listen ${app_port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${app_port}>/" /etc/apache2/sites-available/*.conf

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan config:cache
php artisan view:cache

exec apache2-foreground

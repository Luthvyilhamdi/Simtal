#!/bin/bash
#
# Script update/deploy Simtal (simtal.ldcpim.id)
# Server: Niagahoster/Hostinger - PHP 8.3
#
# Cara pakai di server:
#   ~/deploy-simtal.sh
#
set -e
cd ~/public_html/simtal.ldcpim.id

echo "==> [1/4] Pull dari GitHub..."
git pull

echo "==> [2/4] Composer install..."
php -d disable_functions= /usr/local/bin/composer install --no-dev --optimize-autoloader

echo "==> [3/4] Migrate database..."
php artisan migrate --force

echo "==> [4/4] Refresh cache..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "==> SELESAI! Update berhasil di https://simtal.ldcpim.id"

#!/usr/bin/env bash
#
# deploy.sh — dijalankan DI SERVER (dipanggil GitHub Actions via SSH, atau manual).
# Idempoten: aman dipanggil berulang. Deploy bersih dari origin/main.
#
# Prasyarat di server (lihat deploy/README.md):
#   - repo ter-clone di $APP_DIR sebagai user `deploy`
#   - .env production sudah ada & terisi (TIDAK ditimpa skrip ini)
#   - php8.4-fpm, composer, node/npm, git terpasang
#   - user `deploy` boleh `sudo systemctl reload php8.4-fpm` tanpa password
#
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/geolicense}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
BRANCH="${DEPLOY_BRANCH:-main}"

cd "$APP_DIR"

echo "==> [1/8] Sinkron kode dari origin/$BRANCH"
git fetch --prune origin
git reset --hard "origin/$BRANCH"

echo "==> [2/8] Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> [3/8] Dependensi npm"
npm ci --no-audit --no-fund

echo "==> [4/8] Build asset Vite (dibatasi memori agar aman di RAM 1 GB + swap)"
NODE_OPTIONS=--max-old-space-size=640 npm run build

echo "==> [5/9] Migrasi database"
php artisan migrate --force

echo "==> [6/9] Seeder idempoten (menu sidebar + produk kanonik)"
# seedMenus() di DatabaseSeeder bersifat all-or-nothing, jadi menu baru harus
# di-seed lewat seeder terpisah yang firstOrCreate — aman dipanggil berulang.
php artisan db:seed --class=SystemMenuSeeder --force
php artisan db:seed --class=LicensePlanMenuSeeder --force
# Produk kanonik (GEOCAT, GEOBILL) — SKU harus cocok dengan build client karena
# aktivasi license dicek per-produk. firstOrCreate by sku, aman dipanggil ulang.
php artisan db:seed --class=ProductSeeder --force

echo "==> [7/9] Symlink storage publik"
php artisan storage:link || true

echo "==> [8/9] Optimize (config/route/view/event cache)"
php artisan optimize

echo "==> [9/9] Reload PHP-FPM"
sudo systemctl reload "$PHP_FPM_SERVICE"

echo "==> Deploy selesai: $(git rev-parse --short HEAD) @ $(date -u '+%Y-%m-%d %H:%M:%SZ')"

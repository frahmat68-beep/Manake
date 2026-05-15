#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "==> composer dump-autoload"
composer dump-autoload

echo "==> php artisan config:clear"
php artisan config:clear

echo "==> php artisan route:clear"
php artisan route:clear

echo "==> php artisan view:clear"
php artisan view:clear

echo "==> php artisan migrate:fresh --seed --force"
php artisan migrate:fresh --seed --force

echo "==> php artisan test"
php artisan test

echo "CI checks passed."

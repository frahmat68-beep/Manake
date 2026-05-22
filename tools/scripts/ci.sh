#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

export APP_ENV=testing
export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-database/database.sqlite}"
export CACHE_STORE=array
export SESSION_DRIVER=array
export QUEUE_CONNECTION=sync
export MAIL_MAILER=array

mkdir -p database
touch "$DB_DATABASE"

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

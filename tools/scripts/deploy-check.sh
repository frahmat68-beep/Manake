#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
    echo "ERROR: .env not found. Copy .env.example and set production variables first."
    exit 1
fi

APP_ENV_VALUE="$(php -r "echo trim((string) getenv('APP_ENV'));")"
APP_DEBUG_VALUE="$(php -r "echo trim((string) getenv('APP_DEBUG'));")"

if [[ -z "$APP_ENV_VALUE" ]]; then
    APP_ENV_VALUE="$(grep -E '^APP_ENV=' .env | head -n1 | cut -d'=' -f2- | tr -d '\"' || true)"
fi
if [[ -z "$APP_DEBUG_VALUE" ]]; then
    APP_DEBUG_VALUE="$(grep -E '^APP_DEBUG=' .env | head -n1 | cut -d'=' -f2- | tr -d '\"' || true)"
fi

if [[ "$APP_ENV_VALUE" != "production" ]]; then
    echo "ERROR: APP_ENV must be 'production' for deployment. Current: '${APP_ENV_VALUE:-<empty>}'"
    exit 1
fi

if [[ "$APP_DEBUG_VALUE" != "false" ]]; then
    echo "ERROR: APP_DEBUG must be 'false' for deployment. Current: '${APP_DEBUG_VALUE:-<empty>}'"
    exit 1
fi

echo "==> composer validate"
composer validate --no-check-publish

echo "==> composer install --optimize-autoloader --no-dev --dry-run"
composer install --optimize-autoloader --no-dev --dry-run

echo "==> php artisan optimize:clear"
php artisan optimize:clear

echo "==> php artisan config:cache"
php artisan config:cache

echo "==> php artisan route:cache"
php artisan route:cache

echo "==> php artisan view:cache"
php artisan view:cache

echo "==> php artisan optimize:clear"
php artisan optimize:clear

echo "Deploy checks passed."

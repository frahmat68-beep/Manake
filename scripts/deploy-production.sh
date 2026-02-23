#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "==> Starting production deployment routine"

if [[ ! -f .env ]]; then
    echo "ERROR: .env not found."
    exit 1
fi

if [[ "${SKIP_MAINTENANCE_MODE:-0}" != "1" ]]; then
    echo "==> Enable maintenance mode"
    php artisan down --render="errors::503" || true
fi

echo "==> Validate production environment and warm cache"
bash scripts/deploy-check.sh

if command -v npm >/dev/null 2>&1; then
    echo "==> Install Node dependencies (clean)"
    npm ci

    echo "==> Build frontend assets"
    npm run build
fi

echo "==> Restart queue workers (if any)"
php artisan queue:restart || true

echo "==> Run scheduler once for smoke-check"
php artisan schedule:run || true

if [[ "${SKIP_MAINTENANCE_MODE:-0}" != "1" ]]; then
    echo "==> Disable maintenance mode"
    php artisan up || true
fi

echo "Production deployment routine completed."

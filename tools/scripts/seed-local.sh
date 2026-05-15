#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ -z "${SUPER_ADMIN_EMAIL:-}" || -z "${SUPER_ADMIN_PASSWORD:-}" ]]; then
    echo "SUPER_ADMIN_EMAIL or SUPER_ADMIN_PASSWORD is not set. Skipping seeding."
    exit 0
fi

php artisan db:seed --class=DatabaseSeeder

echo "Database seeding complete."

#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

run_step() {
    local label="$1"
    shift
    echo "==> ${label}"
    if ! "$@"; then
        echo "ERROR: ${label} failed."
        return 1
    fi
}

if [[ ! -f .env ]]; then
    echo "WARNING: .env not found. Copy .env.example and configure your environment."
fi

run_step "composer dump-autoload" composer dump-autoload || {
    echo "Hint: Ensure Composer is installed and vendor/ exists."
    exit 1
}

run_step "php artisan config:clear" php artisan config:clear || {
    echo "Hint: Check PHP CLI and APP_KEY in .env."
    exit 1
}

run_step "php artisan route:clear" php artisan route:clear || {
    echo "Hint: Fix any syntax errors in routes or cached files."
    exit 1
}

run_step "php artisan view:clear" php artisan view:clear || {
    echo "Hint: Check storage and view cache permissions."
    exit 1
}

run_step "php artisan cache:clear" php artisan cache:clear || {
    echo "Hint: Check cache driver configuration and storage permissions."
    exit 1
}

echo "==> php artisan route:list --name=admin"
if ! php artisan route:list --name=admin >/dev/null; then
    echo "ERROR: Admin route registration check failed."
    echo "Hint: Ensure admin routes are registered and route names in Blade are valid."
    exit 1
fi

if command -v rg >/dev/null 2>&1; then
    if rg -n "use Throwable;" database/migrations; then
        echo "WARNING: Found 'use Throwable;' in migrations. Remove the import and use \\Throwable in catch blocks."
    fi
else
    if grep -R --line-number "use Throwable;" database/migrations; then
        echo "WARNING: Found 'use Throwable;' in migrations. Remove the import and use \\Throwable in catch blocks."
    fi
fi

echo "==> php artisan migrate --force"
set +e
migrate_output=$(php artisan migrate --force 2>&1)
migrate_status=$?
set -e
echo "$migrate_output"

if [[ $migrate_status -ne 0 ]]; then
    if echo "$migrate_output" | grep -qiE "already exists|Base table or view already exists|table .* exists"; then
        echo "ERROR: Migration failed because a table already exists."
        echo "Hint: Run php artisan migrate:status, then fix the offending migration or drop the table manually."
        echo "Hint: For local dev only, you may run php artisan migrate:fresh --seed if safe."
        exit 1
    fi
    echo "ERROR: php artisan migrate --force failed."
    echo "Hint: Verify DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env."
    exit 1
fi

if [[ -L public/storage ]]; then
    echo "==> storage symlink check"
    echo "OK: public/storage symlink exists."
else
    echo "WARNING: public/storage symlink is missing. Attempting to create it..."
    set +e
    storage_link_output=$(php artisan storage:link 2>&1)
    storage_link_status=$?
    set -e
    echo "$storage_link_output"

    if [[ $storage_link_status -ne 0 && ! -L public/storage ]]; then
        echo "ERROR: Failed to create public/storage symlink."
        echo "Hint: Ensure web user has write access to public/ and storage/."
        exit 1
    fi
fi

run_step "php artisan test" php artisan test || {
    echo "Hint: Review the failing test output above, then re-run php artisan test."
    exit 1
}

if [[ "${SKIP_FRONTEND_BUILD:-0}" == "1" ]]; then
    echo "==> npm run build (skipped via SKIP_FRONTEND_BUILD=1)"
elif command -v npm >/dev/null 2>&1; then
    run_step "npm run build" npm run build || {
        echo "Hint: If build fails with native binary issues, run: npm install"
        echo "Hint: On macOS, remove quarantine flag on node_modules if needed:"
        echo "      xattr -dr com.apple.quarantine node_modules"
        exit 1
    }
else
    echo "WARNING: npm not found; frontend build check was skipped."
fi

echo "All checks passed."

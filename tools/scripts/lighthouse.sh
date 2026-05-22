#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

URL="${LIGHTHOUSE_URL:-http://127.0.0.1:3000/}"
PORT="${LIGHTHOUSE_PORT:-3000}"
REPORT_DIR="${LIGHTHOUSE_REPORT_DIR:-storage/logs}"
SERVER_PID=""

mkdir -p "$REPORT_DIR"

cleanup() {
    if [[ -n "$SERVER_PID" ]] && kill -0 "$SERVER_PID" >/dev/null 2>&1; then
        kill "$SERVER_PID" >/dev/null 2>&1 || true
    fi
}

is_available() {
    TARGET_URL="$URL" php -r '
        $url = getenv("TARGET_URL");
        $context = stream_context_create(["http" => ["timeout" => 5]]);
        exit(@file_get_contents($url, false, $context) === false ? 1 : 0);
    '
}

if ! is_available; then
    php artisan serve --host=127.0.0.1 --port="$PORT" > "$REPORT_DIR/lighthouse-server.log" 2>&1 &
    SERVER_PID="$!"
    trap cleanup EXIT

    for _ in {1..30}; do
        if is_available; then
            break
        fi
        sleep 1
    done
fi

if ! is_available; then
    echo "Lighthouse target is not reachable: $URL" >&2
    exit 1
fi

./node_modules/.bin/lighthouse "$URL" \
    --only-categories=performance,accessibility,best-practices,seo \
    --output=html \
    --output-path="$REPORT_DIR/lighthouse-report.html" \
    --chrome-flags="--headless=new --no-sandbox"

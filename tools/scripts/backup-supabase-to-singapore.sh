#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

if [[ -f "${ROOT_DIR}/.env" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "${ROOT_DIR}/.env"
  set +a
fi

SOURCE_URL="${SUPABASE_DB_URL:-${DB_URL:-}}"
TARGET_URL="${SINGAPORE_BACKUP_DB_URL:-}"

if [[ -z "${SOURCE_URL}" ]]; then
  echo "SUPABASE_DB_URL atau DB_URL belum diisi." >&2
  exit 1
fi

if [[ -z "${TARGET_URL}" ]]; then
  echo "SINGAPORE_BACKUP_DB_URL belum diisi." >&2
  exit 1
fi

if ! command -v pg_dump >/dev/null 2>&1; then
  echo "pg_dump tidak ditemukan. Install PostgreSQL client tools dulu." >&2
  exit 1
fi

if ! command -v psql >/dev/null 2>&1; then
  echo "psql tidak ditemukan. Install PostgreSQL client tools dulu." >&2
  exit 1
fi

echo "Streaming backup Supabase -> Singapore Postgres..."
pg_dump \
  --dbname="${SOURCE_URL}" \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  --quote-all-identifiers \
  --schema="${SUPABASE_DB_SCHEMA:-public}" \
  | psql \
      --dbname="${TARGET_URL}" \
      --set=ON_ERROR_STOP=on \
      --single-transaction

echo "Backup selesai."

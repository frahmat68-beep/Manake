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
TARGET_SSH_HOST="${SINGAPORE_BACKUP_SSH_HOST:-152.69.218.198}"
TARGET_SSH_USER="${SINGAPORE_BACKUP_SSH_USER:-ubuntu}"
TARGET_SSH_KEY="${SINGAPORE_BACKUP_SSH_KEY:-${HOME}/.ssh/manake_singapore}"
TARGET_SSH_DB_PORT="${SINGAPORE_BACKUP_SSH_DB_PORT:-5433}"
TARGET_DB="${SINGAPORE_BACKUP_DB_DATABASE:-manake_backup}"

if [[ -z "${SOURCE_URL}" ]]; then
  echo "SUPABASE_DB_URL atau DB_URL belum diisi." >&2
  exit 1
fi

echo "Streaming backup Supabase -> Singapore Postgres..."

dump_source() {
  if ! command -v pg_dump >/dev/null 2>&1; then
    echo "pg_dump tidak ditemukan di mesin lokal." >&2
    return 127
  fi

  pg_dump \
    --dbname="${SOURCE_URL}" \
    --clean \
    --if-exists \
    --no-owner \
    --no-privileges \
    --quote-all-identifiers \
    --schema="${SUPABASE_DB_SCHEMA:-public}"
}

if [[ -n "${TARGET_URL}" ]]; then
  if ! command -v pg_dump >/dev/null 2>&1; then
    echo "pg_dump tidak ditemukan. Install PostgreSQL client tools dulu." >&2
    exit 1
  fi

  if ! command -v psql >/dev/null 2>&1; then
    echo "psql tidak ditemukan. Install PostgreSQL client tools dulu." >&2
    exit 1
  fi

  dump_source | psql \
    --dbname="${TARGET_URL}" \
    --set=ON_ERROR_STOP=on \
    --single-transaction
else
  if [[ -z "${TARGET_SSH_HOST}" || -z "${TARGET_SSH_USER}" ]]; then
    echo "SINGAPORE_BACKUP_DB_URL atau SINGAPORE_BACKUP_SSH_HOST/SINGAPORE_BACKUP_SSH_USER belum diisi." >&2
    exit 1
  fi

  if [[ ! "${TARGET_DB}" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "SINGAPORE_BACKUP_DB_DATABASE hanya boleh berisi huruf, angka, dan underscore." >&2
    exit 1
  fi

  if [[ ! "${TARGET_SSH_DB_PORT}" =~ ^[0-9]+$ ]]; then
    echo "SINGAPORE_BACKUP_SSH_DB_PORT harus berupa angka." >&2
    exit 1
  fi

  if [[ ! -f "${TARGET_SSH_KEY/#\~/${HOME}}" ]]; then
    echo "SSH key backup tidak ditemukan: ${TARGET_SSH_KEY}" >&2
    exit 1
  fi

  EXPANDED_SSH_KEY="${TARGET_SSH_KEY/#\~/${HOME}}"
  SSH_TARGET="${TARGET_SSH_USER}@${TARGET_SSH_HOST}"
  SOURCE_URL_B64="$(printf '%s' "${SOURCE_URL}" | base64 | tr -d '\n')"
  TARGET_SCHEMA="${SUPABASE_DB_SCHEMA:-public}"
  TARGET_SCHEMA_B64="$(printf '%s' "${TARGET_SCHEMA}" | base64 | tr -d '\n')"

  ssh -i "${EXPANDED_SSH_KEY}" -o BatchMode=yes -o ConnectTimeout=15 "${SSH_TARGET}" \
    "sudo -n -u postgres psql --port='${TARGET_SSH_DB_PORT}' -tAc \"SELECT 1 FROM pg_database WHERE datname = '${TARGET_DB}'\" | grep -q 1 || sudo -n -u postgres createdb --port='${TARGET_SSH_DB_PORT}' '${TARGET_DB}'"

  if command -v pg_dump >/dev/null 2>&1; then
    dump_source | ssh -i "${EXPANDED_SSH_KEY}" -o BatchMode=yes -o ConnectTimeout=15 "${SSH_TARGET}" \
      "sudo -n -u postgres psql --port='${TARGET_SSH_DB_PORT}' --dbname='${TARGET_DB}' --set=ON_ERROR_STOP=on --single-transaction"
  else
    ssh -i "${EXPANDED_SSH_KEY}" -o BatchMode=yes -o ConnectTimeout=15 "${SSH_TARGET}" \
      "SOURCE_URL_B64='${SOURCE_URL_B64}' TARGET_DB='${TARGET_DB}' TARGET_DB_PORT='${TARGET_SSH_DB_PORT}' TARGET_SCHEMA_B64='${TARGET_SCHEMA_B64}' bash -s" <<'REMOTE_BACKUP'
set -euo pipefail

PG_DUMP_BIN="$(command -v pg_dump || true)"

if [[ -x "/usr/lib/postgresql/17/bin/pg_dump" ]]; then
  PG_DUMP_BIN="/usr/lib/postgresql/17/bin/pg_dump"
fi

if [[ -z "${PG_DUMP_BIN}" ]]; then
  echo "pg_dump tidak ditemukan di server backup." >&2
  exit 1
fi

SOURCE_URL="$(printf '%s' "${SOURCE_URL_B64}" | base64 -d)"
TARGET_SCHEMA="$(printf '%s' "${TARGET_SCHEMA_B64}" | base64 -d)"

"${PG_DUMP_BIN}" \
  --dbname="${SOURCE_URL}" \
  --clean \
  --if-exists \
  --no-owner \
  --no-privileges \
  --quote-all-identifiers \
  --schema="${TARGET_SCHEMA}" \
  | sudo -n -u postgres psql \
      --port="${TARGET_DB_PORT}" \
      --dbname="${TARGET_DB}" \
      --set=ON_ERROR_STOP=on \
      --single-transaction
REMOTE_BACKUP
  fi
fi

echo "Backup selesai."

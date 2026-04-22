# Deployment & Operasional

Dokumen ini merangkum langkah deployment aman untuk Manake Rental.

## 1) Pre-Deployment Gate

Jalankan terlebih dahulu:

```bash
bash scripts/doctor.sh
```

Script ini akan:
- Membersihkan cache
- Menjalankan migrasi paksa
- Memastikan route admin termuat
- Menjalankan test backend
- Menjalankan build frontend

## 2) Deployment Produksi

Jalankan:

```bash
bash scripts/deploy-production.sh
```

Pastikan `.env` berisi:
- `APP_ENV=production`
- `APP_DEBUG=false`
- kredensial database production
- variabel Midtrans production

## 3) Queue & Scheduler

Pilih salah satu runtime manager:
- Supervisor: gunakan file di `deploy/supervisor/`
- systemd: gunakan file di `deploy/systemd/`

## 4) Static Analysis & Formatting

```bash
composer lint:php
composer analyse
npm run lint
```

## 5) Docker

Untuk setup konsisten lokal/CI:

```bash
docker compose up --build
```

## 6) Vercel via GitHub

Konfigurasi yang disarankan saat import repo ke Vercel:

- Framework Preset: `Other`
- Root Directory: `.`
- Build Command: `npm run build`
- Output Directory: `public/build`
- Node.js Version: gunakan default project Vercel atau `22.x`
- Install Command:
  - `npm install`
  - Composer dependency PHP ditangani oleh runtime/deploy flow Vercel PHP

Template env yang aman tanpa secret disediakan di:

- `.env.vercel.example`

Environment variables minimum untuk Vercel:

```env
APP_NAME=Manake
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://<your-vercel-domain>

DB_CONNECTION=pgsql
DB_URL=postgresql://...
DB_SSLMODE=require

LOG_CHANNEL=stderr
LOG_STACK=stderr
LOG_LEVEL=info

SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME=Manake

MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false

SUPER_ADMIN_EMAIL=...
SUPER_ADMIN_PASSWORD=...

ADMIN_DB_EDIT_ENABLED=false
```

Catatan runtime Vercel untuk project ini:

- `SESSION_DRIVER`, `CACHE_STORE`, dan `QUEUE_CONNECTION` akan otomatis dibuat aman untuk Vercel melalui config aplikasi.
- Queue async jangka panjang tidak cocok dijalankan di Vercel serverless tanpa layanan worker terpisah.
- File upload lokal di `storage/app/public` tidak persisten antar deployment. Jika admin akan sering upload aset produksi, pindahkan media ke object storage eksternal.
- Upload media admin sekarang bisa diarahkan ke disk lain dengan `SITE_MEDIA_DISK` seperti `s3`, sehingga Vercel tidak bergantung pada filesystem lokal untuk logo, hero, dan gambar equipment.
- Jangan commit file `.env.production` atau secret produksi ke repository. Simpan semuanya di Vercel Project Settings -> Environment Variables.
- Pastikan `composer.lock` tetap ikut ada di repository/deployment agar dependency PHP yang dipakai Vercel deterministik.

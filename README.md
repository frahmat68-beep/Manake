# Manake Rental (Laravel 12)

Manake Rental adalah aplikasi rental alat produksi dengan alur end-to-end:
- Login/register user
- Checkout rental
- Pembayaran Midtrans Snap
- Generate resi setelah pembayaran lunas
- Admin CMS untuk konten website + upload gambar
- Editor teks user-facing per halaman (landing, catalog, kategori, footer)

## Fitur Utama

- Public storefront: home, catalog, category, product detail, footer dinamis
- User area: overview, booking history, pembayaran, receipt
- Admin panel:
  - Dashboard
  - Categories CRUD
  - Equipments CRUD
  - Orders monitor + update status
  - Users list + reset password (tanpa lihat password asli)
  - Content Manager (text + image)
  - Editor Teks Website (text-only, per halaman user)
  - Website Settings (brand/SEO/social/logo/favicon)
- Audit log admin (`audit_logs`)

## Setup Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run dev
php artisan serve
```

## Setup Supabase

Untuk backup/copy data MySQL ke Supabase dan switch koneksi aplikasi:

- `docs/SETUP-SUPABASE.md`

## Quick Context (Untuk Dev/GPT Baru)

- `docs/PROJECT-ARCHITECTURE.md`

## Super Admin Seeder

Seeder `SuperAdminSeeder` membuat / update akun:
- Email diambil dari env `SUPER_ADMIN_EMAIL`
- Nama diambil dari env `SUPER_ADMIN_NAME`
- Role: `super_admin`
- Password diambil dari env `SUPERADMIN_PASSWORD` (fallback `SUPER_ADMIN_PASSWORD`)

Contoh:

```env
SUPER_ADMIN_EMAIL=frahmat68@gmail.com
SUPER_ADMIN_NAME=Fikri Rachmat
SUPERADMIN_PASSWORD=ChangeMe123!
```

## Health Check Script

Gunakan script berikut sebelum release:

```bash
bash scripts/doctor.sh
```

Script akan menjalankan:
- `composer dump-autoload`
- `php artisan config:clear`
- `php artisan route:clear`
- `php artisan view:clear`
- `php artisan cache:clear`
- `php artisan route:list --name=admin` (sanity check route admin)
- `php artisan migrate --force`
- auto-create `public/storage` link bila belum ada
- `php artisan test`
- `npm run build` (jika npm tersedia)

Opsional skip build frontend:

```bash
SKIP_FRONTEND_BUILD=1 bash scripts/doctor.sh
```

## CI/CD

Workflow GitHub Actions ada di `.github/workflows/ci.yml` dengan alur:
- Install dependency PHP dan Node
- Lint frontend (`npm run lint`)
- Lint PHP (`composer lint:php`)
- Static analysis PHP (`composer analyse`, Larastan/PHPStan)
- Test (`npm run test`, `php artisan test`)
- Queue/scheduler smoke-check (`php artisan queue:work --once`, `php artisan schedule:run`)
- Build frontend (`npm run build`)
- Upload artifact `public/build`
- Jalankan `bash scripts/doctor.sh` sebagai **pre-deploy gate**

## Deployment Checklist

1. Environment & key
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Jalankan `php artisan key:generate` bila `APP_KEY` belum ada

2. Database
- Pastikan koneksi DB benar
- Jalankan `php artisan migrate --force`

3. Storage & permissions
- Jalankan `php artisan storage:link`
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh web server

4. Cache
- Jalankan `php artisan config:cache`
- Jalankan `php artisan route:cache`
- Jalankan `php artisan view:cache`

5. Queue & scheduler
- Jika ada job async, jalankan worker queue (Supervisor/systemd)
- Tambahkan cron scheduler:
  - `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

6. HTTPS & security
- Wajib aktifkan HTTPS di reverse proxy/web server
- Set `APP_URL` ke domain HTTPS
- Verifikasi callback Midtrans memakai endpoint HTTPS

7. Midtrans
- Set env berikut:

```env
MIDTRANS_SERVER_KEY=...
MIDTRANS_CLIENT_KEY=...
MIDTRANS_IS_PRODUCTION=false
OTP_REQUIRED=false
OTP_TTL_MINUTES=5
```

Keterangan OTP:
- `OTP_REQUIRED=false`: alur user tetap seperti sekarang (tanpa wajib OTP).
- `OTP_REQUIRED=true`: user baru/login yang belum verifikasi akan diarahkan ke halaman OTP email.

## Script Deployment Otomatis

Gunakan script berikut di server produksi:

```bash
bash scripts/deploy-production.sh
```

Script ini menjalankan:
- Validasi `APP_ENV=production` dan `APP_DEBUG=false`
- `composer install --no-dev`
- `php artisan migrate --force`
- `php artisan config:cache`, `route:cache`, `view:cache`
- `npm ci && npm run build` (jika npm tersedia)
- `php artisan queue:restart`

Untuk validasi saja (tanpa deployment penuh), gunakan:

```bash
bash scripts/deploy-check.sh
```

## Queue & Scheduler Runtime

Template operasional sudah disediakan:
- Supervisor:
  - `deploy/supervisor/manake-queue-worker.conf`
  - `deploy/supervisor/manake-scheduler.conf`
- systemd:
  - `deploy/systemd/manake-queue-worker.service`
  - `deploy/systemd/manake-scheduler.service`
  - `deploy/systemd/manake-scheduler.timer`

## Docker (Opsional)

Untuk environment konsisten lokal/CI:

```bash
docker compose up --build
```

File yang disediakan:
- `Dockerfile` (multi-stage build: Composer + Node + runtime)
- `docker-compose.yml` (app, queue, scheduler, mysql, redis)
- `.dockerignore`

## Security & Audit

## Testing

```bash
npm run lint
composer lint:php
composer analyse
npm run test
php artisan test
```

Audit log admin sudah aktif via tabel `audit_logs` untuk perubahan penting (order/content/category/equipment/DB explorer).
Monitoring error (Sentry/Bugsnag) disiapkan melalui env di `.env.example` agar mudah diaktifkan saat production hardening.

Target release: seluruh test harus green sebelum deploy.

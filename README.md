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

### A. Setup Normal
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev
php artisan serve
```

### B. Low RAM Smoke Check Mode (Rekomendasi Sidang)
Gunakan perintah artisan ringan untuk membersihkan cache & memverifikasi route tanpa menjalankan server berat:
```bash
npm run smoke:php
```
Script di atas menjalankan pembersihan cache views, config, routes, dan mendaftarkan route secara instan.

## Setup Supabase

Untuk backup/copy data MySQL ke Supabase dan switch koneksi aplikasi:
- `docs/SETUP-SUPABASE.md`

## Quick Context (Untuk Dev/GPT Baru)

- `docs/PROJECT-ARCHITECTURE.md`
- `docs/SECURITY-NOTES.md` (Catatan Keamanan TA)
- `docs/SIDANG-TA-CHECKLIST.md` (Panduan Tanya-Jawab Sidang TA)

## Super Admin Seeder

Seeder `SuperAdminSeeder` membuat / update akun:
- Email diambil dari env `SUPER_ADMIN_EMAIL`
- Nama diambil dari env `SUPER_ADMIN_NAME`
- Role: `super_admin`
- Password diambil dari env `SUPERADMIN_PASSWORD` (Hanya untuk local/demo)

> [!IMPORTANT]
> Untuk lingkungan **Produksi (Tugas Akhir)**, sangat direkomendasikan menggunakan env `SUPERADMIN_PASSWORD_HASH` (string Bcrypt) untuk menghindari penyimpanan password super admin secara teks polos (plaintext) di `.env`.

Tambahkan juga konfigurasi keamanan di `.env` produksi:
```env
ADMIN_SYNC_FROM_USERS=false
ADMIN_ALLOW_MANUAL_RESET_LINK=false
APP_DEBUG=false
```

## Security Highlights

Sistem telah dilengkapi dengan pengamanan ringan untuk keperluan Tugas Akhir:
1. **Admin Panel Protection:** Dipisahkan secara struktural lewat Laravel admin session guard.
2. **Anti Payment Override:** Status pembayaran sewa hanya dapat diubah manual oleh `super_admin` (tercatat di audit log).
3. **Midtrans Webhook Validation:** Validasi signature key SHA-512 + pengecekan nominal `gross_amount` callback dengan integer safety.
4. **Signed Invoice URLs:** Menghindari celah IDOR/Insecure Direct Object Reference lewat Temporary Signed Route.
5. **PII Masking di DB Explorer:** Secara otomatis menyensor kata sandi, email, NIK, telepon, dan alamat agar data mentah pribadi tidak bocor.
6. **Chatbot Rate-Limiting:** Middleware throttle bawaan Laravel untuk mencegah spamming API.
7. **Race Condition Stock Lock:** Menggunakan `lockForUpdate()` di tingkat DB Transaction saat checkout.

*Detail selengkapnya dapat dibaca pada [SECURITY-NOTES.md](file:///Users/kiki/Documents/Web%20Develop/Website%20Manake/docs/SECURITY-NOTES.md).*

## Health Check Script

> [!NOTE]
> Script pengetesan dan build otomatis di bawah disarankan dijalankan pada sistem berkinerja tinggi atau sebagai pre-release check. Untuk laptop dengan RAM terbatas, direkomendasikan menggunakan perintah ringan `npm run smoke:php`.

Gunakan script berikut sebelum release (Full CI / release check only):
```bash
bash tools/scripts/doctor.sh
```

Opsional skip build frontend:
```bash
SKIP_FRONTEND_BUILD=1 bash tools/scripts/doctor.sh
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
- Jalankan `bash tools/scripts/doctor.sh` sebagai **pre-deploy gate**

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
bash tools/scripts/deploy-production.sh
```

Untuk validasi saja (tanpa deployment penuh), gunakan:
```bash
bash tools/scripts/deploy-check.sh
```

## Security & Audit

Audit log admin sudah aktif via tabel `audit_logs` untuk perubahan penting (order/content/category/equipment/DB explorer).
Monitoring error (Sentry/Bugsnag) disiapkan melalui env di `.env.example` agar mudah diaktifkan saat production hardening.

## Testing
```bash
npm run lint
composer lint:php
composer analyse
npm run test
php artisan test
```

Target release: seluruh test harus green sebelum deploy.

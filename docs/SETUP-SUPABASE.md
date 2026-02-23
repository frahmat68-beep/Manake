# Supabase Setup (Backup + Integrasi)

Panduan ini untuk:
- backup/copy data dari MySQL lokal ke Supabase PostgreSQL
- lalu ganti koneksi aplikasi Laravel ke Supabase

## Prasyarat

- Project Supabase sudah dibuat.
- Database MySQL lokal (source) aktif dan berisi data.
- Extension `pdo_mysql` dan `pdo_pgsql` aktif (di project ini sudah aktif).

## 1) Ambil Detail Koneksi Supabase

Di dashboard Supabase:
- `Project Settings` -> `Database` -> `Connection string`

Pilih salah satu:

1. URI penuh (direkomendasikan):
```env
SUPABASE_DB_URL=postgresql://postgres:YOUR_PASSWORD@db.<project-ref>.supabase.co:5432/postgres?sslmode=require
```

2. Field terpisah:
```env
SUPABASE_DB_HOST=db.<project-ref>.supabase.co
SUPABASE_DB_PORT=5432
SUPABASE_DB_DATABASE=postgres
SUPABASE_DB_USERNAME=postgres
SUPABASE_DB_PASSWORD=YOUR_PASSWORD
SUPABASE_DB_SSLMODE=require
```

Jika koneksi direct host gagal karena jaringan, gunakan URI pooler dari Supabase (biasanya host `*.pooler.supabase.com`).

## 2) Isi Env Source MySQL

Gunakan koneksi source terpisah agar aman saat nanti `DB_CONNECTION` diganti ke Supabase:

```env
MYSQL_SOURCE_DB_HOST=127.0.0.1
MYSQL_SOURCE_DB_PORT=3306
MYSQL_SOURCE_DB_DATABASE=manake_db
MYSQL_SOURCE_DB_USERNAME=root
MYSQL_SOURCE_DB_PASSWORD=your_mysql_password
```

## 3) Copy Data MySQL -> Supabase

Command ini tersedia:

```bash
php artisan db:copy-mysql-to-supabase --source=mysql_source --target=supabase --migrate-target
```

Keterangan:
- default: target akan di-`TRUNCATE` dulu supaya tidak duplicate.
- `--migrate-target`: jalankan migration ke Supabase sebelum copy data.
- `--dry-run`: cek rencana tanpa menulis data.
- `--append`: append data tanpa truncate (pakai dengan hati-hati).

Contoh dry-run:

```bash
php artisan db:copy-mysql-to-supabase --source=mysql_source --target=supabase --dry-run
```

## 4) Verifikasi Data di Supabase

```bash
php artisan db:show --database=supabase
php artisan db:table users --database=supabase
php artisan db:table orders --database=supabase
```

## 5) Switch Aplikasi ke Supabase

Set `.env` utama aplikasi:

```env
DB_CONNECTION=pgsql
DB_HOST=<host_supabase>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<username_supabase>
DB_PASSWORD=<password_supabase>
DB_SSLMODE=require
```

Lalu:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan migrate:status
```

Setelah ini aplikasi akan langsung memakai Supabase sebagai database utama.

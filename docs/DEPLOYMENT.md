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

# Sidang Tugas Akhir (TA) Checklist & Q&A

Dokumen ringkas ini dibuat untuk membantu Anda menjawab pertanyaan dosen penguji seputar teknis sistem, keamanan, dan arsitektur database saat sidang berlangsung.

---

## 📋 1. Checklist Persiapan Demo Sidang

*   [ ] **Mode Debug Mati:** Pastikan `APP_DEBUG=false` di `.env` demo Anda untuk menunjukkan kesiapan production.
*   [ ] **Koneksi Midtrans Sandbox:** Pastikan `MIDTRANS_IS_PRODUCTION=false` dan Anda menggunakan kartu tes sandbox Midtrans.
*   [ ] **Akun Pengujian Siap:**
    *   `super_admin`: `admin@manake.id` (untuk demonstrasi DB Explorer, audit logs, and payment override).
    *   `admin`: Buat 1 akun admin biasa untuk demonstrasi penolakan ubah status pembayaran manual.
    *   `user`: Buat 1 akun user biasa lengkap dengan profil untuk simulasi sewa barang.
*   [ ] **Seeder & Cache Bersih:** Jalankan `npm run smoke:php` sebelum mulai demo agar memori terbebas dan routing teruji.

---

## 💬 2. Pertanyaan Sidang Penguji & Panduan Jawaban

### ❓ Q1: "Bagaimana sistem Anda mencegah agar pengguna tidak menyewa barang melebihi stok yang ada jika diakses bersamaan (*race condition*)?"
*   **Jawaban Konsep:**
    > *"Sistem kami menggunakan fitur **Database Transaction** (`DB::transaction`) dan penguncian baris (`lockForUpdate()`) di tingkat SQL DBMS saat checkout berlangsung. Ketika ada transaksi pemesanan berjalan, stok item dikunci secara eksklusif bagi transaksi tersebut. Transaksi lain yang mengakses item yang sama di milidetik yang sama dipaksa menunggu hingga transaksi pertama selesai, sehingga mencegah overbooking stok."*

### ❓ Q2: "Mengapa invoice dibuat menggunakan Signed URL daripada membiarkan admin/user mengakses langsung lewat ID pesanan?"
*   **Jawaban Konsep:**
    > *"Hal ini ditujukan untuk memitigasi celah keamanan **IDOR** (Insecure Direct Object Reference). Jika diakses lewat ID atau nomor pesanan langsung tanpa proteksi, pengguna nakal dapat menebak nomor pesanan orang lain lalu mengunduh invoice/data pesanan mereka secara ilegal. Dengan **Signed URL**, Laravel membubuhkan tanda tangan kriptografis berbasis kunci rahasia aplikasi (`APP_KEY`) dengan masa kedaluwarsa 30 menit. Jika tautan tersebut diubah satu karakter saja oleh peretas, tanda tangannya menjadi tidak cocok dan Laravel akan langsung memblokir akses tersebut."*

### ❓ Q3: "Bagaimana cara sistem Anda memvalidasi callback pembayaran dari pihak ketiga (Midtrans) agar tidak dipalsukan?"
*   **Jawaban Konsep:**
    > *"Sistem melakukan validasi berlapis:*
    > 1. *Mengecek **Signature Key** SHA-512 bawaan Midtrans yang diverifikasi menggunakan Server Key aplikasi.*
    > 2. *Mengecek integritas nilai **Gross Amount**. Sistem membandingkan nominal uang di callback dengan nominal pesanan di database. Jika nominal berbeda (indikasi manipulasi harga), transaksi langsung dibatalkan.*
    > 3. *Mencegah **Replay Attack** dengan menyimpan setiap webhook event key yang masuk ke dalam database `payment_webhook_events`. Jika event key yang sama dikirim dua kali, sistem tidak akan memprosesnya ulang."*

### ❓ Q4: "Kenapa ada modul DB Explorer di dalam admin panel? Bukankah itu berbahaya?"
*   **Jawaban Konsep:**
    > *"DB Explorer adalah modul internal khusus untuk super admin guna mengaudit data tabular secara cepat tanpa perlu membuka DBMS client pihak ketiga. Celah ini kami mitigasi dengan:*
    > 1. *Membatasi hak akses hanya untuk `super_admin` dengan verifikasi di level routing via middleware.*
    > 2. *Menerapkan **PII Masking** secara dinamis di tingkat controller. Seluruh data pribadi pengguna (NIK, email, telepon, password hash) disensor otomatis menjadi bintang sebelum dirender ke HTML.*
    > 3. *Menonaktifkan manipulasi data (fitur Edit/Update) secara default pada tabel-tabel utama (`users`, `admins`, `payments`, `audit_logs`)."*

# Security & Hardening Highlights (Manake Rental)

Dokumen ini meringkas sistem pengamanan ringan (*security hardening*) yang diimplementasikan pada website **Manake Rental** untuk kebutuhan sidang Tugas Akhir (TA) dan audit keamanan kode.

---

## 🔒 1. Ringkasan 8 Proteksi Keamanan Utama

### 🛡️ 1.1. Admin Panel & Role Separation (Guard Protection)
*   **Guard Separation:** Akses admin dipisahkan secara struktural dari guard user menggunakan Laravel session guard khusus (`admin`).
*   **Role-Based Access Control (RBAC):** Akun dibagi menjadi sub-role `admin` biasa dan `super_admin`. Fitur sensitif seperti DB Explorer, reset password langsung, dan penayangan tautan manual dilindungi ketat dan didelegasikan hanya untuk level `super_admin`.

### 💵 1.2. Anti Payment Override (Admin Payment Bypass Block)
*   **Admin Biasa:** Diblokir dari mengubah `status_pembayaran` secara manual (paid/refunded/failed/expired) pada `OrderController@update`. Tombol override dinonaktifkan di UI detail order. Admin biasa hanya diizinkan memperbarui status operasional rental (misal: memproses pengambilan barang jika pembayaran lunas).
*   **Super Admin:** Satu-satunya role yang diizinkan melakukan koreksi manual `status_pembayaran`. Setiap kali Super Admin melalukan intervensi manual, sistem otomatis merekam detail status sebelum/sesudah di dalam **Audit Log** (`audit_logs`) secara realtime.

### 💳 1.3. Midtrans Webhook Signature Verification
*   Sistem memverifikasi integritas payload callback dari Midtrans menggunakan **Signature Key** SHA-512 yang menggabungkan `order_id`, `status_code`, `gross_amount`, dan `Server Key` rahasia di `.env`. 
*   Callback tanpa signature valid ditolak langsung dengan status HTTP 403 (*Forbidden*).

### 🔢 1.4. Webhook Gross Amount & Type Safety
*   **Gross Amount Check:** Sistem memvalidasi kecocokan nilai `gross_amount` dari payload callback dengan nominal transaksi asli yang tersimpan di database. Jika terdeteksi ketidakcocokan nominal (upaya manipulasi harga oleh penyerang), callback akan dibatalkan (`Gross amount mismatch`) dan status order tidak akan berubah menjadi `paid` (HTTP 422).
*   **Decimal Normalization:** Sistem melakukan standardisasi matematis (`PaymentController@normalizeGrossAmount`) sehingga format string desimal callback (contoh: `"100000.00"`) dinormalisasi secara presisi menjadi integer `100000` sebelum dicocokkan, menghindari false-positive kegagalan pencocokan tipe data.

### 📜 1.5. Signed Invoice URLs (Anti-ID-Guessing/Insecure Direct Object Reference)
*   Invoice/kwitansi sewa hanya dapat diakses melalui **Temporary Signed URL** (`URL::temporarySignedRoute`) yang menggunakan tanda tangan kriptografis berbasis kunci aplikasi (`APP_KEY`). Tautan otomatis kedaluwarsa dalam 30 menit.
*   Mencegah penyerang menebak nomor pesanan orang lain (ID-Guessing / IDOR) untuk mengunduh invoice sepihak tanpa login.

### 👁️ 1.6. Dynamic PII Masking di DB Explorer
*   Modul DB Explorer (`DbExplorerController`) secara otomatis menyamarkan data sensitif pribadi (*Personally Identifiable Information* - PII) menggunakan pencocokan kata kunci (*heuristics*) seperti `password`, `token`, `payload`, `snap_token`, `email`, `phone`, `nik`, `address`, `emergency`, `birth`, dan `maps`.
*   Data sensitif disensor menjadi bintang (seperti `u***@domain.com` atau `081******456`) sebelum dirender ke view HTML, sehingga admin tidak dapat menyalahgunakan data mentah pengguna.

### 💬 1.7. Rate-Limiting & Abuse Prevention di Chatbot
*   Mengamankan API Chatbot menggunakan middleware throttling bawaan Laravel:
    *   Endpoint `/chatbot/message` dibatasi maksimum `10` request per menit.
    *   Endpoint `/chatbot/reset` dibatasi maksimum `20` request per menit.
*   Melindungi server dari pemborosan kuota API (Gemini/Ollama) akibat serangan spamming atau bot.

### 📦 1.8. Race Condition & Stock Validation (DB Transaction & Lock)
*   Logika checkout menyewa alat produksi dibungkus dalam **Database Transaction** (`DB::transaction`) dan menggunakan penguncian baris (`lockForUpdate()`) di tingkat database.
*   Mencegah *race condition* (overbooking) jika dua pengguna menekan tombol checkout secara bersamaan untuk alat yang stoknya tersisa satu.

---

## 🔑 2. Rekomendasi Hardening Produksi (Tugas Akhir)

1.  **SUPERADMIN_PASSWORD_HASH:**
    *   Di server produksi, hindari menulis plaintext `SUPERADMIN_PASSWORD` di file `.env`. Gunakan `SUPERADMIN_PASSWORD_HASH` dengan memasukkan string hasil bcrypt (contoh: `$2y$12$...`).
2.  **ADMIN_SYNC_FROM_USERS:**
    *   Set `ADMIN_SYNC_FROM_USERS=false` di `.env` produksi untuk mencegah sinkronisasi otomatis user biasa menjadi admin.
3.  **ADMIN_ALLOW_MANUAL_RESET_LINK:**
    *   Set `ADMIN_ALLOW_MANUAL_RESET_LINK=false` di `.env` produksi untuk mencegah penayangan tautan manual reset password di layar admin saat email gagal terkirim.
4.  **APP_DEBUG:**
    *   Set `APP_DEBUG=false` untuk mencegah kebocoran *stack trace* rahasia database ke pengunjung umum jika terjadi error 500.

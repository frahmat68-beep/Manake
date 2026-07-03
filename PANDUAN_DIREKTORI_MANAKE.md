# PETA NAVIGASI CODEBASE (PANDUAN DIREKTORI) — WEBSITE MANAKE

Dokumen ini adalah peta ringkas untuk membantu Anda menemukan berkas (file) penting saat ditunjuk oleh dosen penguji selama sidang Tugas Akhir (TA).

---

## 1. STRUKTUR UTAMA FOLDER & FUNGSI NYA

*   📂 **`app/Http/Controllers/`** ➔ **Logika Fitur (Otak)**
    *   Disinilah letak semua file Controller yang mengatur jalannya alur data website.
    *   *Paling Penting*:
        *   `CheckoutController.php` ➔ Mengatur alur pemesanan & transaksi.
        *   `CartController.php` ➔ Mengatur keranjang & bentrok tanggal sewa.
        *   `PaymentController.php` ➔ Menangani status pembayaran & integrasi Midtrans.
        *   `OrderController.php` ➔ Menangani riwayat sewa & reschedule sewa.
        *   `AvailabilityBoardController.php` ➔ Menghitung ketersediaan papan rental.

*   📂 **`app/Services/`** ➔ **Logika Kalkulasi Khusus**
    *   `AvailabilityService.php` ➔ Mengitung stok sisa & buffer day.
    *   `PricingService.php` ➔ Menghitung subtotal, PPN 11%, denda terlambat.
    *   `MidtransService.php` ➔ Membuat payload & Snap Token untuk dikirim ke API Midtrans.

*   📂 **`app/Models/`** ➔ **Representasi Tabel Database**
    *   `Order.php` ➔ Representasi tabel `orders` (berisi constants status sewa).
    *   `Equipment.php` ➔ Representasi tabel `equipments` (alat rental).
    *   `User.php` ➔ Representasi tabel `users` (penyewa).

*   📂 **`resources/views/`** ➔ **Tampilan Web (User Interface / Blade)**
    *   `welcome.blade.php` ➔ Halaman utama (Homepage/Beranda).
    *   `about.blade.php` ➔ Halaman tentang kami.
    *   📂 `partials/` ➔ Potongan tampilan bersama (Navbar, Footer, loader).
    *   📂 `equipments/` ➔ Tampilan katalog (`index.blade.php`) & detail produk (`show.blade.php`).
    *   📂 `auth/` ➔ Tampilan Login, Register, Verifikasi Email, & Lupa Password.
    *   📂 `profile/` ➔ Tampilan pengisian & verifikasi profil penyewa.

*   📂 **`routes/`** ➔ **Daftar URL Website (Rute)**
    *   `web.php` ➔ Semua alamat URL (rute) website yang bisa diakses user & admin.

*   📂 **`database/migrations/`** ➔ **Struktur Tabel Database**
    *   File-file pembuat skema tabel di Supabase.

---

## 2. JIKA DOSEN MEMINTA PERUBAHAN INSTAN:

1.  **"Ganti warna tombol, logo, navbar, footer, background, teks"**
    ➔ Buka folder `resources/views/`, lalu cari berkas Blade yang sesuai dengan tampilan tersebut.

2.  **"Tunjukkan di mana data order disimpan ke database"**
    ➔ Buka `app/Http/Controllers/CheckoutController.php` lalu cari fungsi `store()`.

3.  **"Tunjukkan bagaimana cara mendeteksi bentrok jadwal sewa"**
    ➔ Buka `app/Services/AvailabilityService.php` lalu cari fungsi `evaluateRange()` atau `getDailyReservedUnits()`.

4.  **"Tunjukkan kode penerima webhook / callback sukses dari Midtrans"**
    ➔ Buka `app/Http/Controllers/PaymentController.php` lalu cari fungsi `handleNotification()`.

5.  **"Ganti durasi penguncian booking pending (Hold Window)"**
    ➔ Buka `app/Services/AvailabilityService.php` lalu ubah nilai konstanta `HOLD_WINDOW_MINUTES`.

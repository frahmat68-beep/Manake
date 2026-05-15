# SKRIPSI: Website Manake AI Integration

## Judul Skripsi (Draft)
**Pengembangan Sistem Rental Alat Produksi Terintegrasi dengan Asisten AI Berbasis Model Llama 3.1 Menggunakan NVIDIA NIM API**

---

## Ringkasan Teknis (Untuk Bab III & IV)

### 1. Arsitektur Chatbot (Manake Guide)
Sistem chatbot diimplementasikan sebagai asisten pintar yang membantu pengguna melakukan navigasi katalog dan memahami prosedur penyewaan.

- **Backend**: Laravel 12.
- **AI Engine**: NVIDIA NIM (Meta Llama 3.1 8B Instruct).
- **Communication Protocol**: REST API melalui `NvidiaAiService.php`.
- **UI Framework**: Alpine.js untuk reaktivitas frontend dan Tailwind CSS untuk desain premium (glassmorphism).

### 2. Mekanisme RAG (Retrieval-Augmented Generation) Sederhana
Alih-alih melatih ulang model (fine-tuning), sistem ini menggunakan teknik *prompt injection* secara dinamis:
1. Setiap pesan masuk akan memicu `buildSystemPrompt()` di backend.
2. Backend mengambil data *live* dari database:
   - Nama & Tagline Situs.
   - Daftar Kategori Alat.
   - Daftar Alat yang tersedia (Equipment Registry) beserta harga dan stok.
3. Data ini disuntikkan ke dalam `system prompt` AI sebelum dikirim ke server NVIDIA.
4. Hasilnya, AI mampu memberikan jawaban yang sangat akurat mengenai stok barang yang benar-benar ada di database Manake.

### 3. Keandalan & Keamanan (High Availability)
- **Fallback Logic**: Implementasi dua API Key. Jika kunci utama terkena *rate limit* (429) atau *unauthorized* (401), sistem secara otomatis beralih ke kunci cadangan.
- **Rate Limiting**: Dibatasi 10 pesan per menit per user untuk mencegah penyalahgunaan API quota.
- **Context Protection**: Data user yang bersifat pribadi (password, email, data transaksi orang lain) secara eksplisit tidak dimasukkan ke dalam konteks AI.

### 4. Aturan Bisnis dalam AI
AI telah diprogram untuk memahami aturan unik Manake:
- **1-Day Buffer**: AI mengetahui bahwa setiap penyewaan memerlukan jeda 1 hari untuk pengecekan kualitas.
- **Alur Transaksi**: AI dapat memandu user dari pemilihan alat hingga pembayaran melalui Midtrans Snap.

---

## Kutipan Bibliografi (Otomatis)
- NVIDIA Corporation. (2024). *NVIDIA NIM API Documentation*. [Online]. Tersedia: https://build.nvidia.com/
- Meta AI. (2024). *Llama 3.1 Model Card and Documentation*.
- Otwell, T. (2025). *Laravel Documentation v12.x*. [Online]. Tersedia: https://laravel.com/docs/

---

*Catatan: File ini dibuat secara otomatis oleh Antigravity untuk dokumentasi skripsi.*

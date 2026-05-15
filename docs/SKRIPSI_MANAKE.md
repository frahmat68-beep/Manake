# SKRIPSI: Website Manake AI Integration

## Judul Skripsi (Draft)
**Pengembangan Sistem Rental Alat Produksi Terintegrasi dengan Asisten AI Berbasis Model Qwen2 Menggunakan Infrastruktur Local AI (Ollama)**

---

## Ringkasan Teknis (Untuk Bab III & IV)

### 1. Arsitektur Chatbot (Manake Guide)
Sistem chatbot diimplementasikan sebagai asisten pintar yang membantu pengguna melakukan navigasi katalog dan memahami prosedur penyewaan.

- **Backend**: Laravel 12.
- **AI Engine**: Local AI / Ollama (Qwen2 0.5B / 1.5B).
- **Communication Protocol**: REST API melalui `LocalAiService.php`.
- **UI Framework**: Alpine.js untuk reaktivitas frontend dan Tailwind CSS untuk desain premium (glassmorphism).

### 2. Mekanisme RAG (Retrieval-Augmented Generation) Sederhana
Alih-alih melatih ulang model (fine-tuning), sistem ini menggunakan teknik *prompt injection* secara dinamis:
1. Setiap pesan masuk akan memicu `buildSystemPrompt()` di backend.
2. Backend mengambil data *live* dari database:
   - Nama & Tagline Situs.
   - Daftar Kategori Alat.
   - Daftar Alat yang tersedia (Equipment Registry) beserta harga dan stok.
3. Data ini disuntikkan ke dalam `system prompt` AI sebelum dikirim ke mesin AI lokal.
4. Hasilnya, AI mampu memberikan jawaban yang sangat akurat mengenai stok barang yang benar-benar ada di database Manake.

### 3. Keandalan & Keamanan (Local Infrastructure)
- **Local Processing**: Seluruh data diproses di server internal, menjamin privasi data perusahaan.
- **Rate Limiting**: Dibatasi 10 pesan per menit per user untuk menjaga stabilitas resource server.
- **Context Protection**: Data user yang bersifat pribadi (password, email, data transaksi orang lain) secara eksplisit tidak dimasukkan ke dalam konteks AI.

### 4. Aturan Bisnis dalam AI (Strict Scope)
AI telah diprogram untuk memahami aturan unik Manake:
- **1-Day Buffer**: AI mengetahui bahwa setiap penyewaan memerlukan jeda 1 hari untuk pengecekan kualitas.
- **Alur Transaksi**: AI dapat memandu user dari pemilihan alat hingga pembayaran melalui Midtrans Snap.
- **Strict Filtering**: AI secara otomatis mengalihkan pertanyaan non-sistem (umum) kembali ke konteks operasional Manake.

---

## Kutipan Bibliografi (Otomatis)
- Ollama Project. (2025). *Ollama API Documentation & Model Library*. [Online]. Tersedia: https://ollama.com/
- Alibaba Cloud. (2024). *Qwen2 Large Language Model Series*.
- Otwell, T. (2025). *Laravel Documentation v12.x*. [Online]. Tersedia: https://laravel.com/docs/

---

*Catatan: File ini diperbarui oleh Antigravity untuk sinkronisasi sistem AI terbaru.*

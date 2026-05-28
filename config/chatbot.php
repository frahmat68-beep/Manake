<?php

return [
    'welcome_message' => env(
        'CHATBOT_WELCOME_MESSAGE',
        'Halo! Saya Manake Guide. Saya bisa bantu soal sewa alat, ketersediaan, buffer 1 hari, pembayaran, pengambilan, dan pengembalian.'
    ),

    'fallback_intro' => env(
        'CHATBOT_FALLBACK_INTRO',
        'Saya belum bisa memakai mesin AI penuh sekarang, tapi saya tetap bisa bantu dengan panduan Manake yang sudah tersedia.'
    ),

    'faqs' => [
        [
            'question' => 'Bagaimana cara sewa alat di Manake?',
            'answer' => 'Pilih alat di katalog, tentukan tanggal sewa, masukkan ke keranjang, lanjut checkout, isi data pengambilan, lalu bayar lewat Midtrans. Setelah pembayaran sukses, Anda bisa ambil alat sesuai jadwal.',
            'keywords' => ['cara sewa', 'alur sewa', 'booking', 'checkout', 'rent', 'sewa alat'],
        ],
        [
            'question' => 'Apa itu aturan buffer 1 hari?',
            'answer' => 'Setiap alat memakai buffer 1 hari sebelum dan sesudah masa sewa untuk pengecekan kualitas dan maintenance. Jadi tanggal di sekitar booking bisa ikut tertutup walau bukan tanggal pemakaian utama.',
            'keywords' => ['buffer', '1 day buffer', 'hari jeda', 'tanggal bentrok', 'availability', 'ketersediaan'],
        ],
        [
            'question' => 'Bagaimana cek ketersediaan alat?',
            'answer' => 'Gunakan Availability Board untuk melihat jadwal aktif per tanggal, atau buka detail produk untuk cek ketersediaan sesuai rentang tanggal sewa dan jumlah unit yang dibutuhkan.',
            'keywords' => ['cek alat', 'availability board', 'ketersediaan', 'stok', 'tersedia', 'tanggal'],
        ],
        [
            'question' => 'Pembayaran memakai apa?',
            'answer' => 'Pembayaran utama memakai Midtrans Snap, sehingga metode seperti Virtual Account, QRIS, dan e-wallet yang didukung Midtrans bisa digunakan sesuai channel yang aktif.',
            'keywords' => ['pembayaran', 'bayar', 'midtrans', 'qris', 'gopay', 'virtual account'],
        ],
        [
            'question' => 'Di mana lokasi Manake dan jam operasionalnya?',
            'answer' => 'Lokasi operasional yang dipakai sistem adalah Manake Studio di Lampung, dengan jam operasional 09:00 sampai 21:00 WIB. Untuk titik detail dan maps, cek bagian kontak atau footer website.',
            'keywords' => ['lokasi', 'alamat', 'maps', 'jam operasional', 'studio', 'lampung'],
        ],
        [
            'question' => 'Bagaimana jika ingin reschedule?',
            'answer' => 'Reschedule hanya bisa dilakukan jika tanggal baru masih lolos aturan buffer, stok tersedia, dan durasi sewanya tetap sesuai aturan sistem. Detail final tetap dicek ulang saat proses perubahan jadwal.',
            'keywords' => ['reschedule', 'ubah jadwal', 'ganti tanggal', 'jadwal baru'],
        ],
        [
            'question' => 'Apa yang terjadi jika alat rusak atau ada denda tambahan?',
            'answer' => 'Jika ada biaya tambahan seperti kerusakan, sistem bisa membuat tagihan tambahan terpisah. Status order tidak dianggap benar-benar selesai sampai kewajiban tambahan yang relevan sudah dibereskan.',
            'keywords' => ['rusak', 'denda', 'biaya tambahan', 'damage fee', 'kerusakan'],
        ],
        [
            'question' => 'Apakah bisa cek katalog tanpa login?',
            'answer' => 'Ya, Anda bisa melihat seluruh daftar alat, spesifikasi, dan harga sewa di katalog kami secara bebas tanpa perlu login terlebih dahulu.',
            'keywords' => ['cek katalog', 'tanpa login', 'katalog bebas', 'lihat alat'],
        ],
        [
            'question' => 'Kapan harus login?',
            'answer' => 'Login hanya wajib dilakukan saat Anda ingin menambahkan alat ke keranjang belanja (cart) dan melanjutkan proses checkout booking sewa.',
            'keywords' => ['kapan login', 'login wajib', 'harus login', 'masuk akun'],
        ],
        [
            'question' => 'Bagaimana cara cek jadwal alat?',
            'answer' => 'Anda bisa melihat jadwal aktif di Availability Board untuk memantau tanggal ketersediaan unit, atau langsung memilih rentang tanggal di halaman detail alat.',
            'keywords' => ['cek jadwal', 'board', 'ketersediaan', 'tanggal kosong'],
        ],
        [
            'question' => 'Bagaimana kalau tanggal alat penuh?',
            'answer' => 'Jika tanggal yang Anda inginkan sudah terisi atau tertutup oleh buffer 1 hari, silakan pilih tanggal alternatif lain atau hubungi admin untuk rekomendasi unit sejenis.',
            'keywords' => ['tanggal penuh', 'penuh', 'bentrok', 'alat alternatif'],
        ],
        [
            'question' => 'Apa yang perlu disiapkan sebelum ambil alat?',
            'answer' => 'Siapkan kartu identitas asli (KTP/SIM) penyewa yang sesuai dengan data booking untuk diverifikasi oleh tim studio saat serah terima alat.',
            'keywords' => ['sebelum ambil', 'syarat ambil', 'verifikasi identitas', 'ktp'],
        ],
        [
            'question' => 'Apakah bisa tanya admin untuk rekomendasi alat?',
            'answer' => 'Tentu! Anda bisa masuk ke halaman kontak untuk mendapatkan bantuan langsung dari tim admin dalam merekomendasikan paket alat produksi yang paling sesuai.',
            'keywords' => ['tanya admin', 'rekomendasi', 'bantuan paket', 'admin help'],
        ],
    ],
];

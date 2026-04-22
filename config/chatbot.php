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
    ],
];

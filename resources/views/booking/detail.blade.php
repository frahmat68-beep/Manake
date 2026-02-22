<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pemesanan | Manake Rental</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100">

<!-- HEADER -->
<div class="bg-white border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="{{ asset('manake-logo-blue.png') }}" class="h-8">
        </a>

        <a href="{{ route('overview') }}" class="text-sm text-slate-600 hover:underline">
            ← Kembali ke Riwayat
        </a>
    </div>
</div>

<!-- CONTENT -->
<div class="max-w-6xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- LEFT -->
    <div class="lg:col-span-2 space-y-6">

        <!-- EQUIPMENT -->
        <div class="bg-white rounded-2xl shadow p-6 flex gap-6">
            <img
                src="https://images.unsplash.com/photo-1519183071298-a2962be96c68"
                class="h-32 w-32 object-contain bg-slate-100 rounded-xl">

            <div>
                <h2 class="text-xl font-semibold">Sony A7 III</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Kamera Mirrorless Full Frame
                </p>

                <div class="mt-4 space-y-1 text-sm">
                    <p><b>Durasi:</b> 3 Hari</p>
                    <p><b>Tanggal:</b> 12 Feb – 15 Feb</p>
                    <p><b>Kode Pesanan:</b> MNK-240212</p>
                </div>
            </div>
        </div>

        <!-- STATUS TIMELINE -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Status Pemesanan</h3>

            <div class="space-y-6">

                <!-- STEP -->
                <div class="flex gap-4">
                    <div class="h-4 w-4 rounded-full bg-green-600 mt-1"></div>
                    <div>
                        <p class="font-medium">Pesanan Dibuat</p>
                        <p class="text-sm text-slate-500">
                            12 Feb 2024, 10:21
                        </p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="h-4 w-4 rounded-full bg-yellow-400 mt-1"></div>
                    <div>
                        <p class="font-medium">Menunggu Pembayaran</p>
                        <p class="text-sm text-slate-500">
                            Silakan selesaikan pembayaran
                        </p>
                    </div>
                </div>

                <div class="flex gap-4 opacity-40">
                    <div class="h-4 w-4 rounded-full bg-slate-400 mt-1"></div>
                    <div>
                        <p class="font-medium">Sedang Disewa</p>
                    </div>
                </div>

                <div class="flex gap-4 opacity-40">
                    <div class="h-4 w-4 rounded-full bg-slate-400 mt-1"></div>
                    <div>
                        <p class="font-medium">Selesai</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="space-y-6">

        <!-- PAYMENT -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Ringkasan Pembayaran</h3>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Harga Sewa</span>
                    <span>Rp 350.000 / hari</span>
                </div>

                <div class="flex justify-between">
                    <span>Durasi</span>
                    <span>3 Hari</span>
                </div>

                <hr>

                <div class="flex justify-between font-bold text-lg text-blue-600">
                    <span>Total</span>
                    <span>Rp 1.050.000</span>
                </div>
            </div>

            <a href="#"
               class="block mt-6 text-center
                      bg-blue-600 text-white
                      py-3 rounded-xl font-semibold
                      hover:bg-blue-700 transition">
                Bayar Sekarang
            </a>
        </div>

        <!-- INFO -->
        <div class="bg-blue-50 border border-blue-100
                    rounded-2xl p-4 text-sm text-blue-700">
            Setelah pembayaran dikonfirmasi, alat dapat diambil
            sesuai jadwal yang ditentukan.
        </div>

    </div>

</div>

<!-- FOOTER -->
<footer class="bg-slate-900 text-white mt-20">
    <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm">
        &copy; 2024 Manake Rental
    </div>
</footer>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemesanan | Manake Rental</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100">

<!-- HEADER -->
<div class="bg-white border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center gap-4">
        <a href="/">
            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" alt="Manake" img-class="h-8 w-auto" />
        </a>
        <span class="text-sm text-slate-500">Pemesanan Alat</span>
    </div>
</div>

<!-- CONTENT -->
<div class="max-w-4xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">

    <!-- LEFT -->
    <div class="md:col-span-2 bg-white rounded-2xl shadow p-6 space-y-6">

        <h1 class="text-xl font-semibold">
            Sony A7 III
        </h1>

        <!-- DATE -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-slate-600">Tanggal Mulai</label>
                <input type="date"
                    class="w-full mt-1 rounded-xl border border-slate-300 px-4 py-2
                           focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="text-sm text-slate-600">Tanggal Selesai</label>
                <input type="date"
                    class="w-full mt-1 rounded-xl border border-slate-300 px-4 py-2
                           focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
        </div>

        <!-- NOTE -->
        <div class="bg-blue-50 text-blue-700 text-sm p-4 rounded-xl">
            Harga sewa dihitung per hari.  
            Pengambilan & pengembalian di lokasi Manake.
        </div>

    </div>

    <!-- RIGHT -->
    <div class="bg-white rounded-2xl shadow p-6 space-y-4">

        <h2 class="font-semibold">
            Ringkasan Pemesanan
        </h2>

        <div class="flex justify-between text-sm">
            <span>Harga / hari</span>
            <span>Rp 350.000</span>
        </div>

        <div class="flex justify-between text-sm">
            <span>Durasi</span>
            <span>3 hari</span>
        </div>

        <hr>

        <div class="flex justify-between font-semibold text-lg">
            <span>Total</span>
            <span class="text-blue-600">Rp 1.050.000</span>
        </div>

        <button
            class="w-full mt-4 px-4 py-3 rounded-xl
                   bg-blue-600 text-white font-semibold
                   hover:bg-blue-700 transition">
            Sewa Sekarang
        </button>

        <p class="text-xs text-center text-slate-500">
            * Login diperlukan untuk melanjutkan pemesanan
        </p>

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

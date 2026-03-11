<!DOCTYPE html>
<html>
<head>
    <title>Admin – Detail Pemesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/theme.css'])
</head>
<body class="bg-slate-100 p-10">

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-8">

    <h1 class="text-2xl font-semibold mb-6">Detail Pemesanan #1</h1>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <p><b>Nama Pengguna:</b> Kiki</p>
        <p><b>Email:</b> kiki@email.com</p>

        <p><b>Alat:</b> Sony A7 III</p>
        <p><b>Durasi:</b> 3 Hari</p>

        <p><b>Tanggal:</b> 12 Feb – 15 Feb</p>
        <p><b>Total:</b> Rp 1.050.000</p>

        <p><b>Status:</b>
            <span class="ml-2 px-3 py-1 rounded-full text-xs
                         bg-yellow-100 text-yellow-700">
                Menunggu
            </span>
        </p>
    </div>

    <div class="mt-8 flex gap-3">
        <a href="{{ route('admin.bookings.index') }}"
           class="px-5 py-2 rounded-lg bg-slate-200">
            Kembali
        </a>

        <button class="px-5 py-2 rounded-lg bg-green-600 text-white">
            Tandai Selesai
        </button>
    </div>

</div>

</body>
</html>

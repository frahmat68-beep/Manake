<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin – Daftar Pemesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8">

<div class="max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Pemesanan Masuk</h1>
        <a href="{{ route('overview') }}" class="text-sm text-blue-600">← Ringkasan Pengguna</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-slate-600">
                <tr>
                    <th class="p-4 text-left">ID</th>
                    <th class="p-4 text-left">Pengguna</th>
                    <th class="p-4 text-left">Alat</th>
                    <th class="p-4 text-left">Tanggal</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>

                <!-- ROW 1 -->
                <tr class="border-t">
                    <td class="p-4">#1</td>
                    <td class="p-4">Kiki</td>
                    <td class="p-4">Sony A7 III</td>
                    <td class="p-4">12–15 Feb</td>
                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-xs
                                     bg-yellow-100 text-yellow-700">
                            Menunggu
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('admin.bookings.show', 1) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs">
                            Detail
                        </a>
                    </td>
                </tr>

                <!-- ROW 2 -->
                <tr class="border-t">
                    <td class="p-4">#2</td>
                    <td class="p-4">Budi</td>
                    <td class="p-4">Godox SL60W</td>
                    <td class="p-4">1–3 Feb</td>
                    <td class="p-4">
                        <span class="px-3 py-1 rounded-full text-xs
                                     bg-green-100 text-green-700">
                            Selesai
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('admin.bookings.show', 2) }}"
                           class="px-4 py-2 bg-slate-700 text-white rounded-lg text-xs">
                            Detail
                        </a>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>

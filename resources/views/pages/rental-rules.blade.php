@extends('layouts.app')

@section('title', 'Aturan Sewa Manake')
@section('meta_description', 'Panduan lengkap aturan sewa Manake: pemesanan, pembayaran, reschedule, buffer, denda keterlambatan, dan tanggung jawab penyewa.')

@php
    $rulesKicker = setting('copy.rules_page.kicker', 'Aturan Sewa');
    $rulesTitle = setting('copy.rules_page.title', 'Panduan Sewa Manake Rental');
    $rulesSubtitle = setting('copy.rules_page.subtitle', 'Halaman ini merangkum aturan utama supaya proses sewa aman, jelas, dan adil untuk semua pengguna. Aturan ini berlaku untuk pemesanan website, reschedule, dan pengelolaan unit.');
    $rulesOperationalTitle = setting('copy.rules_page.operational_title', 'Catatan Operasional');
    $rulesPrimaryCta = setting('copy.rules_page.cta_primary', 'Mulai Sewa dari Katalog');
    $rulesSecondaryCta = setting('copy.rules_page.cta_secondary', 'Hubungi Tim Manake');
@endphp

@section('content')
    <section class="mx-auto max-w-6xl space-y-6">
        <div class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500">{{ $rulesKicker }}</p>
            <h1 class="mt-2 text-3xl font-extrabold text-blue-700">{{ $rulesTitle }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
                {{ $rulesSubtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">1. Pemesanan & Pembayaran</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Pemesanan dianggap aktif setelah checkout berhasil dan pembayaran terkonfirmasi.</li>
                    <li><span class="font-semibold text-blue-700">Kode pembayaran berlaku terbatas</span> (umumnya 60 menit) sebelum status berubah kedaluwarsa.</li>
                    <li>Invoice digital menjadi <span class="italic">bukti transaksi resmi</span> setelah status pembayaran lunas.</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">2. Ketersediaan, Buffer & Stok</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Sistem memakai buffer 1 hari sebelum dan sesudah masa sewa untuk keamanan operasional.</li>
                    <li>Jika tanggal bentrok dan stok tidak cukup, checkout otomatis ditolak.</li>
                    <li>Kalender ketersediaan menampilkan hari kosong, hari disewa, dan hari buffer agar keputusan sewa lebih akurat.</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">3. Reschedule Pesanan</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Reschedule hanya bisa sebelum status pengambilan barang berjalan.</li>
                    <li>Durasi hari sewa harus <span class="font-semibold text-blue-700">tetap sama</span> dengan pesanan awal.</li>
                    <li>Jumlah unit/item yang dipesan juga harus <span class="font-semibold text-blue-700">tetap sama</span>.</li>
                    <li>Jika stok tidak tersedia di tanggal baru, sistem menolak reschedule dan menampilkan tanggal bentrok.</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold">4. Denda & Tanggung Jawab</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Keterlambatan 3 jam: denda 30% dari biaya sewa harian.</li>
                    <li>Keterlambatan 6 jam: denda 50% dari biaya sewa harian.</li>
                    <li>Di atas 9 jam: denda 100% dari biaya sewa harian.</li>
                    <li>Kerusakan unit: minimal 50% sesuai assessment admin.</li>
                    <li>Kehilangan unit: penggantian 100% harga unit.</li>
                </ul>
            </article>
        </div>

        <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <h2 class="text-lg font-semibold">{{ $rulesOperationalTitle }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                Tim admin berhak melakukan verifikasi data, validasi stok terakhir, dan pengecekan kondisi alat saat pengambilan/pengembalian. Jika ada perbedaan data atau kendala lapangan, keputusan operasional admin menjadi acuan final.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    {{ $rulesPrimaryCta }}
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                    {{ $rulesSecondaryCta }}
                </a>
            </div>
        </article>
    </section>
@endsection

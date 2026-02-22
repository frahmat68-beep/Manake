@extends('layouts.app')

@section('title', 'Dashboard Pengguna')

@section('content')
    @php
        $stats = [
            [
                'label' => 'Total Riwayat',
                'value' => 12,
                'note' => '+2 minggu ini',
                'accent' => 'text-slate-900',
            ],
            [
                'label' => 'Rental Aktif',
                'value' => 2,
                'note' => '2 alat sedang disewa',
                'accent' => 'text-blue-600',
            ],
            [
                'label' => 'Selesai',
                'value' => 10,
                'note' => '3 selesai bulan ini',
                'accent' => 'text-emerald-600',
            ],
        ];

        $activeRentals = [
            [
                'id' => 1,
                'slug' => 'sony-a7-iii',
                'name' => 'Sony A7 III',
                'date' => '12 Feb – 15 Feb 2026',
                'status' => 'Menunggu Pembayaran',
                'badge_class' => 'bg-amber-100 text-amber-700',
                'price' => 1050000,
                'image' => 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id' => 2,
                'slug' => 'dji-ronin-rs3',
                'name' => 'DJI Ronin RS3',
                'date' => '08 Feb – 10 Feb 2026',
                'status' => 'Sedang Disewa',
                'badge_class' => 'bg-blue-100 text-blue-700',
                'price' => 500000,
                'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        $recentBookings = [
            [
                'id' => 101,
                'name' => 'Godox SL60W',
                'date' => '01 Feb – 03 Feb 2026',
                'status' => 'Selesai',
                'badge_class' => 'bg-emerald-100 text-emerald-700',
                'price' => 300000,
            ],
            [
                'id' => 102,
                'name' => 'RODE Wireless GO II',
                'date' => '28 Jan – 30 Jan 2026',
                'status' => 'Dibatalkan',
                'badge_class' => 'bg-rose-100 text-rose-700',
                'price' => 240000,
            ],
            [
                'id' => 103,
                'name' => 'DJI Mini 3 Pro',
                'date' => '20 Jan – 22 Jan 2026',
                'status' => 'Selesai',
                'badge_class' => 'bg-emerald-100 text-emerald-700',
                'price' => 800000,
            ],
        ];
    @endphp

    <section class="bg-slate-50">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Dashboard</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900">Dashboard Pengguna</h1>
                    <p class="text-sm text-slate-600">Ringkasan penyewaan alat produksi Anda.</p>
                </div>
                <a
                    href="{{ route('catalog') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"
                >
                    Sewa Alat Baru
                </a>
            </div>
        </div>
    </section>

    <section class="bg-slate-100">
        <div class="max-w-7xl mx-auto px-6 pb-14">
            <div class="grid grid-cols-1 lg:grid-cols-[260px,1fr] gap-6">
                <aside class="hidden lg:block">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Menu</p>
                        <nav class="mt-4 space-y-2 text-sm font-semibold">
                            <a href="/dashboard" class="flex items-center gap-3 rounded-xl bg-blue-50 px-3 py-2 text-blue-700">
                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                Dashboard
                            </a>
                            <a href="/booking" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50">
                                <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                Riwayat Saya
                            </a>
                            <a href="{{ route('profile.complete') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50">
                                <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                Profil
                            </a>
                        </nav>
                    </div>

                    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                        <p class="text-sm font-semibold text-blue-700">Butuh bantuan cepat?</p>
                        <p class="mt-2 text-xs text-blue-700/80">Tim Manake siap bantu pilih alat yang tepat untuk proyek Anda.</p>
                        <a
                            href="/contact"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-white px-4 py-2 text-xs font-semibold text-blue-700 shadow-sm hover:shadow transition"
                        >
                            Hubungi Admin
                        </a>
                    </div>
                </aside>

                <div class="space-y-8">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($stats as $stat)
                            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                                <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-2xl font-semibold {{ $stat['accent'] }}">{{ $stat['value'] }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $stat['note'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Rental Aktif</h2>
                            <a href="/booking" class="text-sm text-blue-600 hover:text-blue-700">Lihat semua</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach ($activeRentals as $rent)
                                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 rounded-xl border border-slate-100 p-4 hover:shadow-sm transition">
                                    <div class="flex items-center gap-4">
                                        <img
                                            src="{{ $rent['image'] }}"
                                            alt="{{ $rent['name'] }}"
                                            class="h-20 w-20 rounded-xl object-cover bg-slate-100"
                                        >
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $rent['name'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $rent['date'] }}</p>
                                            <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $rent['badge_class'] }}">
                                                {{ $rent['status'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="text-right">
                                            <p class="text-xs text-slate-500">Total</p>
                                            <p class="text-lg font-semibold text-slate-900">
                                                Rp {{ number_format($rent['price'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a
                                                href="{{ route('product.show', $rent['slug']) }}"
                                                class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                                            >
                                                Detail
                                            </a>
                                            <a
                                                href="/booking/pay/{{ $rent['id'] }}"
                                                class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition"
                                            >
                                                Bayar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">Riwayat Terbaru</h2>
                            <a href="/booking/history" class="text-sm text-slate-500 hover:text-blue-600">Riwayat lengkap</a>
                        </div>

                        <div class="mt-4 divide-y divide-slate-100">
                            @foreach ($recentBookings as $booking)
                                <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $booking['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $booking['date'] }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-sm">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $booking['badge_class'] }}">
                                            {{ $booking['status'] }}
                                        </span>
                                        <span class="font-semibold text-slate-700">
                                            Rp {{ number_format($booking['price'], 0, ',', '.') }}
                                        </span>
                                        <a href="/booking/{{ $booking['id'] }}" class="text-blue-600 hover:text-blue-700">Detail</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

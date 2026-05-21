@extends('layouts.user')

@section('title', __('Dashboard Pengguna'))

@section('content')
    @php
        $stats = [
            [
                'label' => 'Total Riwayat',
                'value' => 12,
                'note' => '+2 minggu ini',
                'accent' => 'text-slate-950 dark:text-white',
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
                'badge_class' => 'manake-badge-warning',
                'price' => 1050000,
                'image' => 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'id' => 2,
                'slug' => 'dji-ronin-rs3',
                'name' => 'DJI Ronin RS3',
                'date' => '08 Feb – 10 Feb 2026',
                'status' => 'Sedang Disewa',
                'badge_class' => 'manake-badge-info',
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
                'badge_class' => 'manake-badge-success',
                'price' => 300000,
            ],
            [
                'id' => 102,
                'name' => 'RODE Wireless GO II',
                'date' => '28 Jan – 30 Jan 2026',
                'status' => 'Dibatalkan',
                'badge_class' => 'manake-badge-danger',
                'price' => 240000,
            ],
            [
                'id' => 103,
                'name' => 'DJI Mini 3 Pro',
                'date' => '20 Jan – 22 Jan 2026',
                'status' => 'Selesai',
                'badge_class' => 'manake-badge-success',
                'price' => 800000,
            ],
        ];
    @endphp

    <div class="manake-page">
        <div class="manake-page-frame space-y-6">
            <section class="manake-card animate-fade-up">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="manake-kicker">Management Panel</p>
                        <h1 class="manake-display mt-3 text-4xl font-black text-slate-950 dark:text-white sm:text-5xl">
                            {{ __('Dashboard Pengguna') }}
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base">
                            {{ __('Selamat datang kembali. Pantau semua aktivitas produksi dan penyewaan alat Anda di sini.') }}
                        </p>
                    </div>
                    <a href="{{ route('catalog') }}" class="btn-primary w-full sm:w-auto">
                        {{ __('Sewa Alat Baru') }}
                    </a>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
                <aside class="lg:col-span-3 space-y-6">
                    <div class="manake-card">
                        <p class="manake-kicker">{{ __('Navigasi Utama') }}</p>
                        <nav class="mt-4 space-y-2">
                            <a href="{{ route('overview') }}" class="btn-primary w-full justify-start">
                                {{ __('Dashboard') }}
                            </a>
                            <a href="{{ route('booking.index') }}" class="btn-secondary w-full justify-start">
                                {{ __('Riwayat Saya') }}
                            </a>
                            <a href="{{ route('profile.complete') }}" class="btn-secondary w-full justify-start">
                                {{ __('Profil Akun') }}
                            </a>
                        </nav>
                    </div>

                    <div class="manake-card" style="background: linear-gradient(180deg, rgba(37,99,235,0.12), rgba(37,99,235,0.04));">
                        <h3 class="text-xl font-black text-slate-950 dark:text-white">{{ __('Butuh Bantuan?') }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                            {{ __('Tim support Manake siap membantu operasional produksi Anda.') }}
                        </p>
                        <a href="/contact" class="btn-primary mt-6 w-full">
                            {{ __('Hubungi Admin') }}
                        </a>
                    </div>
                </aside>

                <div class="lg:col-span-9 space-y-6">
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($stats as $stat)
                            <article class="manake-card">
                                <p class="manake-kicker">{{ $stat['label'] }}</p>
                                <p class="mt-3 text-4xl font-black tracking-tight {{ $stat['accent'] }}">{{ $stat['value'] }}</p>
                                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $stat['note'] }}</p>
                            </article>
                        @endforeach
                    </div>

                    <section class="manake-card">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="manake-kicker">{{ __('Rental Aktif') }}</p>
                                <h2 class="manake-heading mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ __('Rental Aktif') }}</h2>
                            </div>
                            <a href="{{ route('booking.index') }}" class="btn-secondary">
                                {{ __('Lihat Semua') }}
                            </a>
                        </div>

                        <div class="mt-6 space-y-4">
                            @foreach ($activeRentals as $rent)
                                <article class="manake-card-soft p-5">
                                    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="h-20 w-20 overflow-hidden rounded-2xl bg-slate-100 p-2 dark:bg-slate-900/60">
                                                <img src="{{ $rent['image'] }}" alt="{{ $rent['name'] }}" class="h-full w-full object-contain">
                                            </div>
                                            <div>
                                                <p class="text-lg font-black text-slate-950 dark:text-white">{{ $rent['name'] }}</p>
                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $rent['date'] }}</p>
                                                <span class="manake-badge {{ $rent['badge_class'] }} mt-3">{{ $rent['status'] }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="text-left xl:text-right">
                                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Estimasi Biaya') }}</p>
                                                <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">Rp {{ number_format($rent['price'], 0, ',', '.') }}</p>
                                            </div>
                                            <a href="{{ route('product.show', $rent['slug']) }}" class="btn-secondary">
                                                {{ __('Detail') }}
                                            </a>
                                            <a href="/booking/pay/{{ $rent['id'] }}" class="btn-primary">
                                                {{ __('Bayar') }}
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="manake-card">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="manake-kicker">{{ __('Riwayat Terbaru') }}</p>
                                <h2 class="manake-heading mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ __('Riwayat Terbaru') }}</h2>
                            </div>
                            <a href="/booking/history" class="text-sm font-bold text-blue-600 hover:text-blue-700">
                                {{ __('Lengkap') }}
                            </a>
                        </div>

                        <div class="mt-6 divide-y divide-slate-200/70 dark:divide-slate-700">
                            @foreach ($recentBookings as $booking)
                                <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-900/60 dark:text-slate-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-950 dark:text-white">{{ $booking['name'] }}</p>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $booking['date'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="manake-badge {{ $booking['badge_class'] }}">{{ $booking['status'] }}</span>
                                        <p class="text-lg font-black text-slate-950 dark:text-white">Rp {{ number_format($booking['price'], 0, ',', '.') }}</p>
                                        <a href="/booking/{{ $booking['id'] }}" class="btn-secondary">
                                            {{ __('Buka') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

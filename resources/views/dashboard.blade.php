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

    <div class="bg-slate-50 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 py-12 pb-24 sm:px-6 lg:px-8">
            {{-- Header Section --}}
            <header class="mb-12 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between animate-fade-up">
                <div class="glass-lg noise-overlay spotlight-shell rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-1.5 w-8 rounded-full bg-blue-600"></span>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Management Panel</p>
                    </div>
                    <h1 class="text-4xl font-black tracking-tight text-slate-950 sm:text-5xl leading-tight">
                        {{ __('Dashboard Pengguna') }}
                    </h1>
                    <p class="mt-4 text-lg text-slate-600 font-medium max-w-2xl leading-relaxed">
                        {{ __('Selamat datang kembali. Pantau semua aktivitas produksi dan penyewaan alat Anda di sini.') }}
                    </p>
                </div>
                <div class="flex items-center gap-4 px-4 sm:px-0">
                    <a href="{{ route('catalog') }}" class="btn-primary group flex items-center justify-center rounded-2xl px-8 py-4 font-black tracking-widest uppercase text-xs shadow-2xl shadow-blue-600/30">
                        {{ __('Sewa Alat Baru') }}
                        <svg class="ml-2.5 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-start lg:gap-16">
                {{-- Sidebar Navigation --}}
                <aside class="lg:col-span-3 space-y-6 animate-fade-up">
                    <div class="glass-sm noise-overlay rounded-[2.5rem] p-6 border border-white/40 shadow-xl">
                        <p class="px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">{{ __('Navigasi Utama') }}</p>
                        <nav class="space-y-2">
                            <a href="/dashboard" class="group flex items-center gap-4 rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition-all">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Dashboard
                            </a>
                            <a href="/booking" class="group flex items-center gap-4 rounded-2xl px-5 py-4 text-sm font-bold text-slate-600 transition-all hover:bg-white hover:shadow-md hover:text-blue-600">
                                <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat Saya
                            </a>
                            <a href="{{ route('profile.complete') }}" class="group flex items-center gap-4 rounded-2xl px-5 py-4 text-sm font-bold text-slate-600 transition-all hover:bg-white hover:shadow-md hover:text-blue-600">
                                <svg class="h-5 w-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Profil Akun
                            </a>
                        </nav>
                    </div>

                    <div class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[2.5rem] border border-blue-600/20 bg-blue-600 p-8 shadow-2xl text-white">
                        <div class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                        <h3 class="text-xl font-black">{{ __('Butuh Bantuan?') }}</h3>
                        <p class="mt-3 text-sm font-bold text-blue-100 leading-relaxed">{{ __('Tim support Manake siap membantu operasional produksi Anda.') }}</p>
                        <a href="/contact" class="mt-8 flex w-full items-center justify-center rounded-2xl bg-white px-6 py-4 text-xs font-black uppercase tracking-widest text-blue-600 shadow-xl transition-all hover:scale-[1.03] active:scale-95">
                            {{ __('Hubungi Admin') }}
                        </a>
                    </div>
                </aside>

                {{-- Main Stats & Content --}}
                <div class="lg:col-span-9 space-y-10">
                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 animate-fade-up" style="animation-delay: 100ms">
                        @foreach ($stats as $stat)
                            <article class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/40 p-8 transition-all hover:-translate-y-1 hover:shadow-2xl">
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4">{{ $stat['label'] }}</p>
                                    <p class="text-4xl font-black tracking-tighter {{ $stat['accent'] }}">{{ $stat['value'] }}</p>
                                    <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-100/80 px-2.5 py-1 text-[11px] font-bold text-slate-500">
                                        <div class="h-1.5 w-1.5 rounded-full bg-slate-400"></div>
                                        {{ $stat['note'] }}
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Active Rentals Section --}}
                    <section class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[3rem] border border-white/20 bg-white/40 p-10 shadow-2xl animate-fade-up" style="animation-delay: 200ms">
                        <div class="flex items-center justify-between mb-10">
                            <h2 class="text-2xl font-black text-slate-950 flex items-center gap-3">
                                <span class="h-8 w-1.5 rounded-full bg-blue-600"></span>
                                {{ __('Rental Aktif') }}
                            </h2>
                            <a href="/booking" class="text-xs font-black uppercase tracking-widest text-blue-600 hover:underline decoration-2 underline-offset-4">
                                {{ __('Lihat Semua') }}
                            </a>
                        </div>

                        <div class="space-y-6">
                            @foreach ($activeRentals as $rent)
                                <div class="group relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-8 rounded-[2rem] border border-slate-200/60 bg-white/60 p-6 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-600/5">
                                    <div class="flex items-center gap-6">
                                        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-[1.5rem] bg-white border border-slate-100 shadow-sm p-2 group-hover:shadow-md transition-all">
                                            <img src="{{ $rent['image'] }}" alt="{{ $rent['name'] }}" class="h-full w-full object-contain">
                                        </div>
                                        <div>
                                            <p class="text-lg font-black text-slate-950 group-hover:text-blue-700 transition-colors">{{ $rent['name'] }}</p>
                                            <p class="text-sm font-bold text-slate-500 flex items-center gap-2 mt-1">
                                                <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ $rent['date'] }}
                                            </p>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center rounded-xl px-4 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $rent['badge_class'] }} bg-opacity-10 border border-current border-opacity-20">
                                                    {{ $rent['status'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-6 border-t border-slate-100 pt-6 xl:border-0 xl:pt-0">
                                        <div class="text-left xl:text-right">
                                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Estimasi Biaya</p>
                                            <p class="text-2xl font-black text-slate-950 tracking-tighter">
                                                Rp {{ number_format($rent['price'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('product.show', $rent['slug']) }}" class="flex items-center justify-center rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95">
                                                Detail
                                            </a>
                                            <a href="/booking/pay/{{ $rent['id'] }}" class="flex items-center justify-center rounded-2xl bg-blue-600 px-8 py-3.5 text-[11px] font-black uppercase tracking-widest text-white shadow-xl shadow-blue-600/20 transition-all hover:scale-[1.03] active:scale-95">
                                                Bayar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Recent History Section --}}
                    <section class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[3rem] border border-white/20 bg-white/40 p-10 shadow-2xl animate-fade-up" style="animation-delay: 300ms">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-black text-slate-950 flex items-center gap-3">
                                <span class="h-8 w-1.5 rounded-full bg-slate-400"></span>
                                {{ __('Riwayat Terbaru') }}
                            </h2>
                            <a href="/booking/history" class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 transition-colors">
                                {{ __('Lengkap') }}
                            </a>
                        </div>

                        <div class="divide-y divide-slate-200/50">
                            @foreach ($recentBookings as $booking)
                                <div class="group flex flex-col gap-4 py-8 sm:flex-row sm:items-center sm:justify-between transition-all hover:translate-x-1">
                                    <div class="flex items-center gap-5">
                                        <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-base font-black text-slate-950 group-hover:text-blue-700 transition-colors">{{ $booking['name'] }}</p>
                                            <p class="text-xs font-bold text-slate-500 mt-1">{{ $booking['date'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-6">
                                        <span class="inline-flex items-center rounded-xl px-4 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $booking['badge_class'] }} bg-opacity-10 border border-current border-opacity-20">
                                            {{ $booking['status'] }}
                                        </span>
                                        <p class="text-lg font-black text-slate-900 tracking-tighter">
                                            Rp {{ number_format($booking['price'], 0, ',', '.') }}
                                        </p>
                                        <a href="/booking/{{ $booking['id'] }}" class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition-all hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-600/20 active:scale-90">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
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

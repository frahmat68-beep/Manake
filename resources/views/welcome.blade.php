@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@php
    $heroTitle = setting('home.hero_title', setting('hero_title', site_content('home.hero_title')));
    $heroSubtitle = setting('home.hero_subtitle', setting('hero_subtitle', site_content('home.hero_subtitle')));

    $productsReady = collect($productsReady ?? []);
    $guestRentalSnapshot = collect($guestRentalSnapshot ?? []);
    $recentUserOrders = collect($recentUserOrders ?? []);

    $dynamicCategories = collect($navCategories ?? [])
        ->pluck('name')
        ->filter()
        ->take(6)
        ->values();

    if ($dynamicCategories->isEmpty()) {
        $dynamicCategories = collect(['Kamera', 'Lighting', 'Audio', 'HT', 'Drone', 'Stabilizer']);
    }

    $featuredProducts = $productsReady->take(7)->values();
    $heroBackdropImage = collect($featuredProducts)
        ->first(function ($item) {
            $image = (string) data_get($item, 'image_url', '');

            return $image !== '' && ! str_contains($image, 'MANAKE-FAV-M.png');
        });
    $heroBackdropUrl = $heroBackdropImage ? (string) data_get($heroBackdropImage, 'image_url', '') : site_asset('manake-logo-white.png');

    $heroStats = [
        ['label' => __('Rented today'), 'value' => max($guestRentalSnapshot->count(), 1)],
        ['label' => __('Available gear'), 'value' => $productsReady->count()],
        ['label' => __('Upcoming bookings'), 'value' => $recentUserOrders->count()],
    ];

    $howToSteps = [
        ['number' => '01', 'title' => __('Pilih Gear'), 'body' => __('Telusuri kamera, lighting, audio, HT, drone, dan stabilizer yang siap disewa.')],
        ['number' => '02', 'title' => __('Atur Tanggal'), 'body' => __('Pilih tanggal, durasi, dan detail produksi lewat alur checkout yang ringkas.')],
        ['number' => '03', 'title' => __('Konfirmasi'), 'body' => __('Lihat total biaya, cek ketersediaan, lalu kirim pesanan dengan jelas.')],
        ['number' => '04', 'title' => __('Pickup / Kirim'), 'body' => __('Ambil langsung atau atur pengiriman sesuai kebutuhan produksi Anda.')],
    ];

    $footerWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $footerAddress = setting('footer.address', setting('footer_address', site_content('footer.address', __('app.footer.address_body'))));
    $footerAddressLines = collect(preg_split('/\R+/', trim((string) $footerAddress)))
        ->map(static fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $footerAddressTitle = $footerAddressLines->first();
@endphp

@section('content')
    <section class="relative isolate min-h-[100svh] overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0">
            <img src="{{ $heroBackdropUrl }}" alt="" class="h-full w-full object-cover opacity-35 blur-[1px] saturate-75" />
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(2,6,23,0.28)_0%,rgba(2,6,23,0.46)_40%,rgba(2,6,23,0.8)_100%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(251,191,36,0.18),transparent_28%),radial-gradient(circle_at_80%_15%,rgba(15,23,42,0.2),transparent_25%),linear-gradient(90deg,rgba(2,6,23,0.82)_0%,rgba(2,6,23,0.46)_44%,rgba(2,6,23,0.12)_100%)]"></div>
        </div>

        <div class="relative mx-auto flex min-h-[100svh] w-full max-w-[1440px] flex-col px-4 sm:px-6 lg:px-8">
            <div class="flex-1 pt-8 sm:pt-10 lg:pt-12">
                <div class="grid min-h-[calc(100svh-8rem)] items-center">
                    <div class="max-w-3xl space-y-7 pb-8 lg:pb-16">
                        <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/20 bg-black/30 px-4 py-2 text-[11px] font-black uppercase tracking-[0.26em] text-amber-100 backdrop-blur-md">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            {{ __('Professional rental gear') }}
                        </div>

                        <h1 class="max-w-4xl font-serif text-[3.05rem] font-semibold tracking-[-0.04em] text-white leading-[0.95] sm:text-6xl sm:leading-[0.93] lg:text-[6.2rem] lg:leading-[0.92]">
                            <span class="block">{{ __('Rent premium') }}</span>
                            <span
                                x-data="{
                                    categories: @js($dynamicCategories),
                                    index: 0,
                                    init() {
                                        if (! this.categories.length) return;
                                        this.timer = window.setInterval(() => {
                                            this.index = (this.index + 1) % this.categories.length;
                                        }, 2200);
                                    },
                                }"
                                class="block"
                            >
                                <span class="block text-amber-400" x-text="categories[index] || 'Kamera'"></span>
                                <span class="block">{{ __('gear, on demand.') }}</span>
                            </span>
                        </h1>

                        <p class="max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                            {{ $heroSubtitle ?: __('Manake menyediakan kamera, lighting, audio, drone, stabilizer, dan HT untuk production crew, event organizer, mahasiswa, dan filmmaker.') }}
                        </p>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-full border border-amber-300/30 bg-amber-400 px-6 py-3.5 text-sm font-black uppercase tracking-[0.14em] text-slate-950 transition hover:-translate-y-0.5 hover:bg-amber-300">
                                {{ __('Browse Gear') }}
                            </a>
                            <a href="{{ route('rental.rules') }}" class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-5 py-3.5 text-sm font-bold text-white/90 backdrop-blur-sm transition hover:bg-white/10">
                                {{ __('How it works') }}
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section pt-6">
        <div class="ui-container">
            <div class="ui-card bg-slate-950 text-white">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="ui-kicker text-amber-300">{{ __('Available equipment') }}</div>
                        <h2 class="ui-heading mt-3 text-3xl font-black text-white">{{ __('Featured gear ready to book') }}</h2>
                    </div>
                    <a href="{{ route('catalog') }}" class="text-sm font-black uppercase tracking-[0.14em] text-amber-300 transition hover:text-amber-200">
                        {{ __('Browse all') }} →
                    </a>
                </div>
                <div class="mt-6 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @forelse ($featuredProducts->take(6) as $item)
                        @php
                            $itemImage = (string) data_get($item, 'image_url', '');
                            $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                            $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                            $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                        @endphp
                        <a href="{{ route('product.show', $item->slug) }}" class="group w-[16rem] shrink-0 snap-start overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/6 transition hover:-translate-y-1 hover:bg-white/10 sm:w-[18rem]">
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-800">
                                @if ($hasRealImage)
                                    <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.86),_rgba(15,23,42,0.98)_62%)]"></div>
                                    <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(251,191,36,0.18),transparent_35%,rgba(255,255,255,0.05)_50%,transparent_72%)]"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="flex h-24 w-24 items-center justify-center rounded-[1.7rem] border border-white/10 bg-white/6 text-5xl font-black tracking-tight text-white/90 shadow-[0_18px_40px_rgba(15,23,42,0.28)] backdrop-blur">
                                            {{ $itemCategoryInitial }}
                                        </div>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                                <span class="absolute left-3 top-3 rounded-full border border-white/15 bg-slate-950/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-100">
                                    {{ __('Tersedia') }}
                                </span>
                            </div>
                            <div class="space-y-1 p-4">
                                <p class="line-clamp-2 text-sm font-bold text-white">{{ data_get($item, 'name') }}</p>
                                <p class="truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-300">{{ $itemCategory }}</p>
                                <p class="text-xs text-slate-300">{{ __('Mulai dari') }} {{ 'Rp ' . number_format((int) data_get($item, 'price_per_day', 0), 0, ',', '.') }} / hari</p>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-[1.2rem] border border-dashed border-white/15 px-4 py-5 text-sm text-slate-300">
                            {{ __('Belum ada item unggulan yang tersedia.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section pt-6" id="about">
        <div class="ui-container">
            <div class="ui-card grid gap-6 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
                <div>
                    <div class="ui-kicker">{{ __('About Manake') }}</div>
                    <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Production gear rental built for crews that move fast.') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        {{ __('Manake membantu event organizer, mahasiswa, filmmaker, dan tim produksi mendapatkan akses cepat ke alat produksi berkualitas, dengan alur booking yang jelas dan bisa dipercaya.') }}
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Verified') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Peralatan dirawat dan disiapkan sebelum disewa.') }}</p>
                    </div>
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Flexible') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Pickup atau pengiriman sesuai lokasi produksi.') }}</p>
                    </div>
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Transparent') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Harga dan status dibuat mudah dibaca sejak awal.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container grid gap-6 lg:grid-cols-[0.92fr_1.08fr]">
            <div class="ui-card">
                <div class="ui-kicker">{{ __('Snapshot Sewa') }}</div>
                <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Aktivitas rental saat ini.') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    {{ __('Ringkasan live untuk membantu user cepat memahami apa yang sedang tersedia, dipakai, dan akan dibooking.') }}
                </p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">{{ __('Disewa Hari Ini') }}</p>
                        <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ max($guestRentalSnapshot->count(), 1) }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">{{ __('Item Tersedia') }}</p>
                        <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $productsReady->count() }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-700 dark:text-amber-400">{{ __('Booking Mendatang') }}</p>
                        <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $recentUserOrders->count() }}</p>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($guestRentalSnapshot->take(3) as $row)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/50">
                            <div>
                                <p class="text-sm font-bold text-slate-950 dark:text-white">{{ data_get($row, 'name', '-') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ data_get($row, 'start_date', '-') }} → {{ data_get($row, 'end_date', '-') }}</p>
                            </div>
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400">{{ data_get($row, 'qty', 0) }} {{ __('unit') }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            {{ __('Belum ada snapshot rental aktif.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-kicker">{{ __('Cara Sewa') }}</div>
                <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Empat langkah mudah, tanpa ribet.') }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($howToSteps as $step)
                        <article class="rounded-[1.4rem] border border-slate-200/80 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-900/50">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-amber-700 dark:text-amber-400">{{ $step['number'] }}</p>
                            <h3 class="mt-3 text-lg font-black text-slate-950 dark:text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $step['body'] }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-400 px-6 py-4 text-sm font-black text-slate-950 shadow-lg shadow-amber-900/15 transition hover:-translate-y-0.5 hover:bg-amber-300">{{ __('Mulai Sewa Sekarang') }}</a>
                    <a href="{{ route('contact') }}" class="btn-secondary px-6 py-4 text-sm">{{ __('Hubungi Kami') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container">
            <div class="ui-card grid gap-6 lg:grid-cols-[1fr_1fr] lg:items-center">
                <div>
                    <div class="ui-kicker">{{ __('Lokasi') }}</div>
                    <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Manake Studio & Rental') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Datang langsung ke studio kami untuk pickup atau cek lokasi sebelum booking.') }}</p>
                    <div class="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('Alamat:') }}</span> {{ $footerAddressTitle ?: __('Manake Studio & Rental') }}</p>
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('WhatsApp:') }}</span> {{ $footerWhatsapp }}</p>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative min-h-[260px] overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.24),_transparent_32%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(30,41,59,0.92))] p-6 text-white">
                        <div class="absolute inset-0 bg-[linear-gradient(120deg,transparent_0,rgba(255,255,255,0.05)_45%,transparent_70%)]"></div>
                        <div class="relative flex h-full flex-col justify-between">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-[0.26em] text-amber-200">{{ __('Temukan kami di') }}</p>
                                <h3 class="text-2xl font-black leading-tight text-white">{{ __('Jakarta / Depok Area') }}</h3>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Pickup') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Cepat dan jelas') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Penggunaan') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Event, film, kreator') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Akses') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Mudah dicek sebelum booking') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

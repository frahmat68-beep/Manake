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
    <section class="ui-section pt-4 sm:pt-6 lg:pt-8">
        <div class="ui-container overflow-hidden rounded-[2rem] border border-slate-200/80 bg-slate-950 text-white shadow-[0_30px_80px_rgba(15,23,42,0.24)]">
            <div class="relative isolate grid gap-8 p-6 sm:p-8 lg:grid-cols-[minmax(0,1.02fr)_minmax(0,0.98fr)] lg:p-10">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.16),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(251,191,36,0.12),_transparent_24%)]"></div>
                <div class="pointer-events-none absolute -right-24 top-8 h-72 w-72 rounded-full bg-amber-500/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-20 bottom-0 h-72 w-72 rounded-full bg-orange-400/10 blur-3xl"></div>

                <div class="relative space-y-6 lg:pr-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-300/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.24em] text-amber-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        {{ __('Rental Peralatan Profesional') }}
                    </div>

                    <h1 class="ui-title max-w-2xl text-4xl font-black tracking-tight text-white sm:text-6xl lg:text-[4.4rem]">
                        {{ $heroTitle ?: __('Rental gear yang terasa premium, cepat, dan sederhana.') }}
                    </h1>

                    <p class="max-w-2xl text-lg leading-8 text-slate-300">
                        {{ $heroSubtitle ?: __('Manake menyediakan kamera, lighting, audio, drone, stabilizer, dan HT untuk event organizer, mahasiswa, filmmaker, dan production crew yang butuh alur booking yang jelas.') }}
                    </p>

                    <div
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
                        class="flex flex-wrap items-center gap-3 rounded-[1.5rem] border border-white/10 bg-white/5 px-4 py-3 backdrop-blur"
                    >
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-amber-200">{{ __('Cocok untuk') }}</span>
                        <template x-for="(category, i) in categories" :key="category + i">
                            <span x-show="index === i" x-transition.opacity.duration.300ms class="text-base font-black tracking-tight text-white" x-text="category"></span>
                        </template>
                        <span class="text-sm text-slate-300">{{ __('produksi, event, dan kreator.') }}</span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-400 px-6 py-4 text-sm font-black text-slate-950 shadow-lg shadow-amber-900/20 transition hover:-translate-y-0.5 hover:bg-amber-300">{{ __('Lihat Peralatan') }}</a>
                        <a href="{{ route('availability.board') }}" class="btn-secondary border-white/15 bg-white/10 px-6 py-4 text-sm text-white backdrop-blur hover:bg-white/15">{{ __('Cek Ketersediaan') }}</a>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($dynamicCategories as $categoryName)
                            @php $categorySlug = \Illuminate\Support\Str::of($categoryName)->slug()->value(); @endphp
                            <a href="{{ route('category.show', $categorySlug) }}" class="rounded-full border border-white/10 bg-white/7 px-4 py-2 text-xs font-bold text-white/90 backdrop-blur transition hover:bg-white/15">
                                {{ $categoryName }}
                            </a>
                        @endforeach
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($heroStats as $stat)
                            <div class="rounded-[1.4rem] border border-white/10 bg-white/7 p-4 backdrop-blur">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-300">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative min-w-0 lg:self-stretch">
                    <div class="absolute -inset-4 rounded-[2rem] bg-amber-500/10 blur-2xl"></div>
                    <div class="relative flex h-full min-h-[34rem] min-w-0 flex-col overflow-hidden rounded-[2rem] border border-white/10 bg-white/7 p-4 shadow-2xl backdrop-blur-xl">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.26em] text-amber-200">{{ __('Featured gear') }}</p>
                                <h2 class="mt-2 text-xl font-black text-white">{{ __('Siap disewa hari ini') }}</h2>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-amber-100">{{ $productsReady->count() }} {{ __('item') }}</span>
                        </div>

                        <div class="mt-5 grid min-w-0 gap-3 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                            <div x-data="{ index: 0, count: {{ max($featuredProducts->count(), 1) }} }" class="min-w-0 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-300">{{ __('Showcase') }}</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/8 text-white transition hover:bg-white/15" @click="index = (index - 1 + count) % count" aria-label="{{ __('Sebelumnya') }}">‹</button>
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/8 text-white transition hover:bg-white/15" @click="index = (index + 1) % count" aria-label="{{ __('Berikutnya') }}">›</button>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    @forelse ($featuredProducts->take(4) as $item)
                                        @php
                                            $itemImage = (string) data_get($item, 'image_url', '');
                                            $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                            $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                                            $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                                        @endphp
                                        <a href="{{ route('product.show', $item->slug) }}" class="group {{ $loop->iteration > 2 ? 'hidden sm:block' : '' }} overflow-hidden rounded-[1.35rem] border border-white/10 bg-white/6 transition hover:-translate-y-1 hover:bg-white/10">
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

                                <div class="overflow-hidden rounded-[1.4rem] border border-white/10 bg-slate-950/45 p-3">
                                    <div class="flex items-center justify-between px-1 pb-3">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Scroll showcase') }}</p>
                                        <a href="{{ route('catalog') }}" class="text-xs font-bold text-amber-100 transition hover:text-white">{{ __('Browse all') }}</a>
                                    </div>
                                    <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-1 pr-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                        @forelse ($featuredProducts->skip(4)->take(3) as $item)
                                            @php
                                                $itemImage = (string) data_get($item, 'image_url', '');
                                                $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                                $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                                                $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                                            @endphp
                                            <a href="{{ route('product.show', $item->slug) }}" class="group w-[13.5rem] shrink-0 snap-start overflow-hidden rounded-[1.35rem] border border-white/10 bg-white/6 transition hover:bg-white/10 sm:w-[14.5rem]">
                                                <div class="relative aspect-[4/3] bg-slate-800">
                                                    @if ($hasRealImage)
                                                        <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                                    @else
                                                        <div class="absolute inset-0 flex items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.8),_rgba(15,23,42,0.98)_68%)] text-4xl font-black text-white/90">{{ $itemCategoryInitial }}</div>
                                                    @endif
                                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/65 via-transparent to-transparent"></div>
                                                </div>
                                                <div class="p-4">
                                                    <p class="line-clamp-2 text-sm font-bold text-white">{{ data_get($item, 'name') }}</p>
                                                    <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-slate-300">{{ $itemCategory }}</p>
                                                    <p class="mt-2 text-xs text-slate-300">{{ __('Lihat detail →') }}</p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="rounded-[1.2rem] border border-dashed border-white/15 px-4 py-5 text-sm text-slate-300">
                                                {{ __('Belum ada item unggulan tambahan.') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-0 flex flex-col gap-3">
                                <div class="rounded-[1.4rem] border border-white/10 bg-slate-950/40 p-4">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Sorotan') }}</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ __('Pilih gear yang paling siap dibooking.') }}</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">{{ __('Kami menyusun halaman ini seperti showcase rental premium, bukan dashboard generik.') }}</p>
                                </div>

                                <div class="rounded-[1.4rem] border border-white/10 bg-slate-950/40 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Featured') }}</p>
                                        <span class="rounded-full border border-white/10 bg-white/8 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-100">{{ __('Ready') }}</span>
                                    </div>
                                    @forelse ($featuredProducts->slice(4, 1) as $item)
                                        @php
                                            $itemImage = (string) data_get($item, 'image_url', '');
                                            $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                            $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                                            $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                                        @endphp
                                        <a href="{{ route('product.show', $item->slug) }}" class="group mt-4 overflow-hidden rounded-[1.3rem] border border-white/10 bg-white/6 transition hover:bg-white/10">
                                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-800">
                                                @if ($hasRealImage)
                                                    <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                                @else
                                                    <div class="absolute inset-0 flex items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.82),_rgba(15,23,42,0.98)_66%)] text-5xl font-black text-white/90">{{ $itemCategoryInitial }}</div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                                            </div>
                                            <div class="space-y-1 p-4">
                                                <p class="text-base font-bold text-white">{{ data_get($item, 'name') }}</p>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-300">{{ $itemCategory }}</p>
                                                <p class="text-xs text-slate-300">{{ __('Mulai dari') }} {{ 'Rp ' . number_format((int) data_get($item, 'price_per_day', 0), 0, ',', '.') }} / hari</p>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="mt-4 rounded-[1.2rem] border border-dashed border-white/15 px-4 py-5 text-sm text-slate-300">
                                            {{ __('Belum ada item unggulan yang tersedia.') }}
                                        </div>
                                    @endforelse
                                </div>

                                <a href="{{ route('catalog') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-400 px-6 py-4 text-sm font-black text-slate-950 shadow-lg shadow-amber-900/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                                    {{ __('Lihat semua peralatan') }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Tersedia sekarang') }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ $productsReady->count() }}</p>
                            </div>
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Snapshot') }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ max($guestRentalSnapshot->count(), 1) }}</p>
                            </div>
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-200">{{ __('Sewa cepat') }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-200">{{ __('Lihat ketersediaan lalu lanjut ke checkout.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container">
            <div class="ui-card grid gap-6 lg:grid-cols-[0.94fr_1.06fr] lg:items-center">
                <div>
                    <div class="ui-kicker">{{ __('Tentang Manake') }}</div>
                    <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Platform rental gear premium untuk produksi yang serius.') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        {{ __('Manake membantu event organizer, mahasiswa, filmmaker, dan tim produksi mendapatkan akses cepat ke alat produksi berkualitas, dengan alur booking yang jelas dan bisa dipercaya.') }}
                    </p>
                    <div class="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('Fokus:') }}</span> {{ __('Kamera, lighting, audio, HT, drone, stabilizer, dan aksesoris.') }}</p>
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('Prioritas:') }}</span> {{ __('Kecepatan booking, ketersediaan jelas, dan tampilan yang meyakinkan.') }}</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Peralatan Terverifikasi') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Setiap item dirawat dan disiapkan sebelum penyewaan supaya tidak ada kejutan saat produksi.') }}</p>
                    </div>
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Fleksibel') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Ambil langsung atau atur pengiriman sesuai kebutuhan proyek Anda.') }}</p>
                    </div>
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Dukungan') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Tim kami siap membantu jika ada kendala teknis atau perubahan jadwal.') }}</p>
                    </div>
                    <div class="ui-card-soft p-4">
                        <p class="ui-kicker">{{ __('Transparan') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Harga, status, dan alur sewa dibuat mudah dipahami sejak halaman pertama.') }}</p>
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

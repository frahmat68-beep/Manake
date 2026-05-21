@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@php
    $heroTitle = setting('home.hero_title', setting('hero_title', site_content('home.hero_title')));
    $heroSubtitle = setting('home.hero_subtitle', setting('hero_subtitle', site_content('home.hero_subtitle')));

    $productsReady = collect($productsReady ?? []);
    $guestRentalSnapshot = collect($guestRentalSnapshot ?? []);
    $recentUserOrders = collect($recentUserOrders ?? []);
    $isLoggedIn = auth('web')->check();

    $dynamicCategories = collect($navCategories ?? [])
        ->pluck('name')
        ->filter()
        ->take(6)
        ->values();

    if ($dynamicCategories->isEmpty()) {
        $dynamicCategories = collect(['Kamera', 'Lighting', 'Audio', 'HT', 'Drone', 'Stabilizer']);
    }

    $featuredProducts = $productsReady->take(6)->values();

    $heroStats = [
        ['label' => __('Disewa Hari Ini'), 'value' => max($guestRentalSnapshot->count(), 1)],
        ['label' => __('Item Tersedia'), 'value' => $productsReady->count()],
        ['label' => __('Pemesanan Mendatang'), 'value' => $recentUserOrders->count()],
    ];

    $howToSteps = [
        ['number' => '01', 'title' => __('Pilih Peralatan'), 'body' => __('Telusuri katalog dan filter berdasarkan kategori, status, atau budget harian.')],
        ['number' => '02', 'title' => __('Ajukan Pemesanan'), 'body' => __('Isi tanggal sewa, durasi, dan detail produksi Anda di checkout yang ringkas.')],
        ['number' => '03', 'title' => __('Ambil atau Terima'), 'body' => __('Ambil di studio atau minta pengiriman jika lokasi produksi Anda memerlukan itu.')],
        ['number' => '04', 'title' => __('Shoot & Kembalikan'), 'body' => __('Gunakan gear dengan tenang, lalu kembalikan sesuai jadwal dan invoice.')],
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
            <div class="relative isolate grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.02fr_0.98fr] lg:p-10">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(96,165,250,0.18),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_24%)]"></div>
                <div class="pointer-events-none absolute -right-24 top-12 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-28 bottom-0 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative space-y-6 lg:pr-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/8 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-blue-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        {{ __('Rental Peralatan Profesional') }}
                    </div>

                    <h1 class="ui-title max-w-2xl text-4xl font-black tracking-tight text-white sm:text-6xl">
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
                        class="flex flex-wrap items-center gap-3 rounded-[1.5rem] border border-white/10 bg-white/6 px-4 py-3 backdrop-blur"
                    >
                        <span class="text-xs font-bold uppercase tracking-[0.24em] text-blue-200">{{ __('Cocok untuk') }}</span>
                        <template x-for="(category, i) in categories" :key="category + i">
                            <span x-show="index === i" x-transition.opacity.duration.300ms class="text-base font-black tracking-tight text-white" x-text="category"></span>
                        </template>
                        <span class="text-sm text-slate-300">{{ __('produksi, event, dan kreator.') }}</span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('catalog') }}" class="btn-primary px-6 py-4 text-sm shadow-lg shadow-blue-600/20">{{ __('Lihat Peralatan') }}</a>
                        <a href="{{ route('availability.board') }}" class="btn-secondary border-white/15 bg-white/10 px-6 py-4 text-sm text-white backdrop-blur hover:bg-white/15">{{ __('Cek Ketersediaan') }}</a>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach ($dynamicCategories as $categoryName)
                            @php $categorySlug = \Illuminate\Support\Str::of($categoryName)->slug()->value(); @endphp
                            <a href="{{ route('category.show', $categorySlug) }}" class="rounded-full border border-white/10 bg-white/8 px-4 py-2 text-xs font-bold text-white/90 backdrop-blur transition hover:bg-white/15">
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

                <div class="relative lg:self-stretch">
                    <div class="absolute -inset-4 rounded-[2rem] bg-blue-500/15 blur-2xl"></div>
                    <div class="relative flex h-full min-h-[34rem] flex-col overflow-hidden rounded-[2rem] border border-white/10 bg-white/8 p-4 shadow-2xl backdrop-blur-xl">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.26em] text-blue-200">{{ __('Hari Ini') }}</p>
                                <h2 class="mt-2 text-xl font-black text-white">{{ __('Siap disewa') }}</h2>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100">{{ $productsReady->count() }} {{ __('item') }}</span>
                        </div>

                        <div class="mt-5 grid gap-3 lg:grid-cols-[1.15fr_0.85fr]">
                            <div class="grid gap-3 sm:grid-cols-2">
                                @forelse ($featuredProducts->take(4) as $item)
                                    @php
                                        $itemImage = (string) data_get($item, 'image_url', '');
                                        $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                        $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                                        $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                                    @endphp
                                    <a href="{{ route('product.show', $item->slug) }}" class="group overflow-hidden rounded-[1.35rem] border border-white/10 bg-white/6 transition hover:-translate-y-1 hover:bg-white/10">
                                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-800">
                                            @if ($hasRealImage)
                                                <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                            @else
                                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.86),_rgba(15,23,42,0.98)_62%)]"></div>
                                                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(59,130,246,0.18),transparent_35%,rgba(255,255,255,0.05)_50%,transparent_72%)]"></div>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="flex h-24 w-24 items-center justify-center rounded-[1.7rem] border border-white/10 bg-white/6 text-5xl font-black tracking-tight text-white/90 shadow-[0_18px_40px_rgba(15,23,42,0.28)] backdrop-blur">
                                                        {{ $itemCategoryInitial }}
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                                            <span class="absolute left-3 top-3 rounded-full border border-white/15 bg-slate-950/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">
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

                            <div class="flex flex-col gap-3">
                                <div class="rounded-[1.4rem] border border-white/10 bg-slate-950/40 p-4">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Sorotan') }}</p>
                                    <p class="mt-2 text-lg font-black text-white">{{ __('Pilih gear yang paling siap dibooking.') }}</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">{{ __('Section ini sengaja dibuat sebagai showcase, bukan sekadar daftar kartu biasa.') }}</p>
                                </div>

                                <div class="relative overflow-hidden rounded-[1.4rem] border border-white/10 bg-slate-950/40 p-4">
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.16),_transparent_35%)]"></div>
                                    <div class="relative grid gap-3">
                                        @forelse ($featuredProducts->slice(4, 1) as $item)
                                            @php
                                                $itemImage = (string) data_get($item, 'image_url', '');
                                                $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                                $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                                                $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                                            @endphp
                                            <a href="{{ route('product.show', $item->slug) }}" class="group overflow-hidden rounded-[1.2rem] border border-white/10 bg-white/6 transition hover:bg-white/10">
                                                <div class="flex gap-3 p-3">
                                                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-[1rem] bg-slate-800">
                                                        @if ($hasRealImage)
                                                            <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover">
                                                        @else
                                                            <div class="absolute inset-0 flex items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.86),_rgba(15,23,42,0.98)_62%)] text-3xl font-black text-white/90">{{ $itemCategoryInitial }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <span class="inline-flex rounded-full border border-white/15 bg-slate-950/60 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">{{ __('Tersedia') }}</span>
                                                        <p class="mt-2 line-clamp-2 text-sm font-bold text-white">{{ data_get($item, 'name') }}</p>
                                                        <p class="mt-1 truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-300">{{ $itemCategory }}</p>
                                                        <p class="mt-1 text-xs text-slate-300">{{ __('Lihat detail') }} →</p>
                                                    </div>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="rounded-[1.2rem] border border-dashed border-white/15 px-4 py-5 text-sm text-slate-300">
                                                {{ __('Belum ada item unggulan yang tersedia.') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <a href="{{ route('catalog') }}" class="btn-primary w-full px-6 py-4 text-sm">
                                    {{ __('Lihat semua peralatan') }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Tersedia sekarang') }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ $productsReady->count() }}</p>
                            </div>
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Snapshot') }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ max($guestRentalSnapshot->count(), 1) }}</p>
                            </div>
                            <div class="rounded-[1.2rem] border border-white/10 bg-slate-950/40 p-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Sewa cepat') }}</p>
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
            <div class="ui-card grid gap-6 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
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
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-700 dark:text-blue-400">{{ __('Disewa Hari Ini') }}</p>
                        <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ max($guestRentalSnapshot->count(), 1) }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-700 dark:text-blue-400">{{ __('Item Tersedia') }}</p>
                        <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $productsReady->count() }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-700 dark:text-blue-400">{{ __('Booking Mendatang') }}</p>
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
                            <p class="text-xs font-bold text-blue-700 dark:text-blue-400">{{ data_get($row, 'qty', 0) }} {{ __('unit') }}</p>
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
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-blue-700 dark:text-blue-400">{{ $step['number'] }}</p>
                            <h3 class="mt-3 text-lg font-black text-slate-950 dark:text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $step['body'] }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" class="btn-primary px-6 py-4 text-sm">{{ __('Mulai Sewa Sekarang') }}</a>
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
                    <div class="relative min-h-[260px] overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.24),_transparent_32%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(30,41,59,0.92))] p-6 text-white">
                        <div class="absolute inset-0 bg-[linear-gradient(120deg,transparent_0,rgba(255,255,255,0.05)_45%,transparent_70%)]"></div>
                        <div class="relative flex h-full flex-col justify-between">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-[0.26em] text-blue-200">{{ __('Temukan kami di') }}</p>
                                <h3 class="text-2xl font-black leading-tight text-white">{{ __('Jakarta / Depok Area') }}</h3>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Pickup') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Cepat dan jelas') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Penggunaan') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Event, film, kreator') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Akses') }}</p>
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

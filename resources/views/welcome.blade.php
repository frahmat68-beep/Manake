@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@php
    $productsReady = collect($productsReady ?? []);
    $guestRentalSnapshot = collect($guestRentalSnapshot ?? []);
    $recentUserOrders = collect($recentUserOrders ?? []);

    $dynamicCategories = collect(['Camera', 'Lighting', 'Audio', 'HT', 'Drone', 'Stabilizer']);
    $navCategories = collect($navCategories ?? [])->pluck('name')->filter()->take(6)->values();
    if ($navCategories->isEmpty()) {
        $navCategories = collect(['Aksesoris', 'Audio', 'Camera', 'Lens', 'Lighting', 'Monitor & Wireless Control']);
    }

    $featuredProducts = $productsReady->take(6)->values();
    $heroBackdropUrl = site_asset('images/hero-bg.jpg');

    $heroStats = [
        ['label' => __('Item peralatan'), 'value' => '500+'],
        ['label' => __('Produksi terlayani'), 'value' => '1.200+'],
        ['label' => __('Rata-rata respons'), 'value' => '48 jam'],
    ];

    $howToSteps = [
        ['number' => '01', 'title' => __('Pilih Peralatan'), 'body' => __('Telusuri katalog kami dan pilih gear yang sesuai untuk produksi Anda.')],
        ['number' => '02', 'title' => __('Ajukan Pemesanan'), 'body' => __('Isi tanggal sewa, durasi, dan detail produksi dengan cepat.')],
        ['number' => '03', 'title' => __('Ambil atau Terima'), 'body' => __('Ambil di studio kami atau atur pengiriman ke lokasi produksi.')],
        ['number' => '04', 'title' => __('Shoot & Kembalikan'), 'body' => __('Gunakan gear, lalu kembalikan sesuai jadwal dengan tenang.')],
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
            <section class="relative min-h-[100svh] overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0">
            <img src="{{ $heroBackdropUrl }}" alt="" class="h-full w-full object-cover object-center opacity-35" />
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(2,6,23,0.15)_0%,rgba(2,6,23,0.4)_40%,rgba(2,6,23,0.9)_100%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,6,23,0.88)_0%,rgba(2,6,23,0.52)_44%,rgba(2,6,23,0.18)_100%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_42%,rgba(255,255,255,0.07),transparent_24%),radial-gradient(circle_at_20%_22%,rgba(251,191,36,0.16),transparent_22%),radial-gradient(circle_at_80%_18%,rgba(255,255,255,0.08),transparent_18%)]"></div>
        </div>

        <div class="relative mx-auto flex min-h-[100svh] w-full max-w-[1440px] flex-col px-4 sm:px-6 lg:px-10">
            <div class="flex-1 pt-20 sm:pt-24 lg:pt-28">
                <div class="max-w-[60rem]">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-black/24 px-4 py-2 text-[11px] font-black uppercase tracking-[0.28em] text-amber-200 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        {{ __('PROFESSIONAL RENTAL GEAR') }}
                    </div>

                    <h1
                        class="mt-6 max-w-[14ch] font-serif text-[clamp(3.6rem,8.2vw,7.6rem)] font-semibold leading-[0.95] tracking-[-0.05em] text-white sm:max-w-[12ch]"
                        style="font-family: 'DM Serif Display', Georgia, serif;"
                    >
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
                            <span class="inline-block text-amber-400" x-text="categories[index] || 'Stabilizer'"></span>
                            <span class="inline-block">{{ __('gear, on demand.') }}</span>
                        </span>
                    </h1>

                    <p class="mt-7 max-w-[42rem] text-lg leading-8 text-slate-300 sm:text-xl">
                        {{ __('Manake provides access to cinema cameras, professional lighting, audio gear, drones, stabilizers, and more. Pick what you need, book fast, and keep production moving without ownership overhead.') }}
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-3 rounded-md bg-amber-400 px-7 py-4 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                            {{ __('Browse Equipment') }}
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('rental.rules') }}" class="inline-flex items-center gap-2 text-sm text-slate-300 transition hover:text-white">
                            {{ __('How it works') }}
                            <span class="inline-block h-4 w-px bg-white/35"></span>
                            <span aria-hidden="true">⌄</span>
                        </a>
                    </div>

                    <div class="mt-16 grid gap-10 sm:grid-cols-3 sm:gap-12">
                        @foreach ($heroStats as $stat)
                            <div>
                            <p class="text-[clamp(2rem,3vw,3rem)] font-black leading-none text-white">{{ $stat['value'] }}</p>
                            <p class="mt-2 text-sm tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            </div>

            <div class="pb-6 text-center text-[11px] font-semibold uppercase tracking-[0.32em] text-slate-300/70 sm:pb-8">
                {{ __('Gulir') }}
            </div>
        </div>
    </section>

    <section id="categories" class="border-y border-slate-200/70 bg-white py-12">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
            <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap text-slate-400">
                @foreach (array_merge($navCategories->all(), $navCategories->all()) as $category)
                    <div class="flex items-center gap-3 shrink-0 px-4 py-2">
                        <span class="text-sm font-semibold uppercase tracking-[0.24em]">{{ $category }}</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="equipment" class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.28em] text-amber-500">{{ __('Peralatan Unggulan') }}</p>
                    <h2 class="mt-3 font-serif text-4xl leading-tight text-slate-950 sm:text-5xl" style="font-family: 'DM Serif Display', Georgia, serif;">
                        {{ __('Tersedia untuk disewa hari ini.') }}
                    </h2>
                </div>
                <a href="{{ route('catalog') }}" class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-500 transition hover:text-amber-600">
                    {{ __('Lihat semua peralatan') }} →
                </a>
            </div>

            <div class="mt-10 overflow-hidden">
                <div class="flex gap-5 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @forelse ($featuredProducts->take(6) as $item)
                        @php
                            $itemImage = (string) data_get($item, 'image_url', '');
                            $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                            $itemCategory = (string) data_get($item, 'category.name', __('Peralatan'));
                            $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Peralatan'), 0, 1));
                        @endphp
                        <a href="{{ route('product.show', $item->slug) }}" class="group shrink-0 w-[calc(100vw-2rem)] sm:w-[calc(50vw-2.5rem)] lg:w-[calc(33.333vw-2.5rem)] xl:w-[calc(25vw-2.5rem)]">
                            <div class="overflow-hidden rounded-[1.8rem] border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.08)] transition-transform duration-300 group-hover:-translate-y-1">
                                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                                    @if ($hasRealImage)
                                        <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.92),_rgba(15,23,42,0.96)_66%)]"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="flex h-24 w-24 items-center justify-center rounded-3xl border border-white/10 bg-white/10 text-5xl font-black text-white/90 backdrop-blur-sm">
                                                {{ $itemCategoryInitial }}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="absolute left-3 top-3 rounded-sm bg-black/55 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-white">
                                        {{ __('Tersedia') }}
                                    </div>
                                </div>
                                <div class="p-5">
                                    <p class="line-clamp-2 text-base font-semibold text-slate-950">{{ data_get($item, 'name') }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-500">{{ $itemCategory }}</p>
                                    <p class="mt-3 text-sm text-slate-500">
                                        {{ __('Mulai dari') }} <span class="text-slate-950">{{ 'Rp ' . number_format((int) data_get($item, 'price_per_day', 0), 0, ',', '.') }}</span> / {{ __('hari') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-sm text-slate-500">
                            {{ __('Belum ada item unggulan yang tersedia.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-[1440px] gap-12 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-10">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.28em] text-amber-500">{{ __('Tentang Manake') }}</p>
                <h2 class="mt-3 max-w-xl font-serif text-4xl leading-tight text-slate-950 sm:text-5xl" style="font-family: 'DM Serif Display', Georgia, serif;">
                    {{ __('Peralatan produksi yang Anda butuhkan, tepat saat Anda membutuhkannya.') }}
                </h2>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                    {{ __('Manake adalah platform rental peralatan media profesional yang dibangun untuk sutradara, sinematografer, kreator konten, dan rumah produksi. Kami menghadirkan hanya gear terbaik berkelas sinema — mulai dari kamera cinema, lensa broadcast, audio profesional, drone, hingga stabilizer.') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['title' => __('Peralatan Terverifikasi'), 'body' => __('Setiap item diuji, dikalibrasi, dan dirawat sebelum setiap penyewaan.')],
                    ['title' => __('Pengambilan & Pengiriman Fleksibel'), 'body' => __('Ambil di studio kami atau minta peralatan dikirim ke lokasi Anda.')],
                    ['title' => __('Terlindungi & Diasuransikan'), 'body' => __('Semua penyewaan dilengkapi opsi perlindungan kerusakan.')],
                    ['title' => __('Dukungan di Lokasi Syuting'), 'body' => __('Tim operator siap membantu sepanjang produksi Anda.')],
                ] as $pillar)
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-6">
                        <h3 class="text-sm font-semibold text-slate-950">{{ $pillar['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $pillar['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-slate-200/70 bg-slate-50 py-20 sm:py-24">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.28em] text-amber-500">{{ __('Ringkasan Live') }}</p>
                    <h2 class="mt-3 font-serif text-4xl leading-tight text-slate-950 sm:text-5xl" style="font-family: 'DM Serif Display', Georgia, serif;">
                        {{ __('Aktivitas rental saat ini.') }}
                    </h2>
                </div>
                <p class="max-w-sm text-sm leading-7 text-slate-500">
                    {{ __('Diperbarui secara real time. Lihat apa yang sedang terjadi di seluruh armada peralatan media kami sekarang.') }}
                </p>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @php
                    $stats = [
                        ['label' => __('Disewa Hari Ini'), 'value' => max($guestRentalSnapshot->count(), 1)],
                        ['label' => __('Item Tersedia'), 'value' => $productsReady->count()],
                        ['label' => __('Pemesanan Mendatang'), 'value' => $recentUserOrders->count()],
                    ];
                @endphp
                @foreach ($stats as $stat)
                    <div class="rounded-[1.4rem] border border-slate-200 bg-white p-8">
                        <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-4 text-5xl font-black text-amber-500">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="cara-sewa" class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
            <div class="max-w-2xl">
                <p class="text-[11px] font-black uppercase tracking-[0.28em] text-amber-500">{{ __('Cara Sewa') }}</p>
                <h2 class="mt-3 font-serif text-4xl leading-tight text-slate-950 sm:text-5xl" style="font-family: 'DM Serif Display', Georgia, serif;">
                    {{ __('Sewa peralatan dalam empat langkah mudah.') }}
                </h2>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($howToSteps as $step)
                    <article class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-amber-500">{{ $step['number'] }}</p>
                        <h3 class="mt-3 text-lg font-semibold text-slate-950">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $step['body'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-12 flex flex-wrap items-center gap-4">
                <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-md bg-amber-400 px-6 py-3.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                    {{ __('Mulai Sewa Sekarang') }}
                </a>
                <a href="{{ route('contact') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-950">
                    {{ __('Hubungi kami jika ada pertanyaan') }}
                </a>
            </div>
        </div>
    </section>
@endsection

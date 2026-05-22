@extends('layouts.landing')

@section('title', 'Manake — Sewa Alat Produksi')

@php
    $heroCategories = ['Kamera', 'Lighting', 'Audio', 'Drone', 'Stabilizer', 'HT'];
    $fallbackEquipmentCards = collect([
        ['name' => 'ARRI Alexa Mini LF', 'slug' => 'arri-alexa-mini-lf', 'category' => 'Kamera', 'available' => true, 'price' => 1200000, 'image' => site_asset('images/camera-arri.jpg'), 'url' => route('catalog')],
        ['name' => 'Aputure 600d Pro', 'slug' => 'aputure-600d-pro', 'category' => 'Lighting', 'available' => true, 'price' => 350000, 'image' => site_asset('images/lighting-aputure.jpg'), 'url' => route('catalog')],
        ['name' => 'RODE NTG5 Boom Kit', 'slug' => 'rode-ntg5-boom-kit', 'category' => 'Audio', 'available' => false, 'price' => 180000, 'image' => site_asset('images/audio-rode.jpg'), 'url' => route('catalog')],
        ['name' => 'DJI Mavic 3 Cine', 'slug' => 'dji-mavic-3-cine', 'category' => 'Drone', 'available' => true, 'price' => 800000, 'image' => site_asset('images/drone-dji.jpg'), 'url' => route('catalog')],
        ['name' => 'DJI Ronin 4D', 'slug' => 'dji-ronin-4d', 'category' => 'Stabilizer', 'available' => true, 'price' => 650000, 'image' => site_asset('images/stabilizer-ronin.jpg'), 'url' => route('catalog')],
        ['name' => 'Motorola DP4800e Kit', 'slug' => 'motorola-dp4800e-kit', 'category' => 'HT', 'available' => true, 'price' => 120000, 'image' => site_asset('images/ht-motorola.jpg'), 'url' => route('catalog')],
    ]);
    $fallbackMarqueeCategories = collect(['Kamera', 'Lighting', 'Audio', 'HT / Walkie', 'Drone', 'Stabilizer']);
    $fallbackImageByCategory = [
        'kamera' => site_asset('images/camera-arri.jpg'),
        'lighting' => site_asset('images/lighting-aputure.jpg'),
        'audio' => site_asset('images/audio-rode.jpg'),
        'drone' => site_asset('images/drone-dji.jpg'),
        'stabilizer' => site_asset('images/stabilizer-ronin.jpg'),
        'ht' => site_asset('images/ht-motorola.jpg'),
        'walkie' => site_asset('images/ht-motorola.jpg'),
    ];
    $resolveStatusLabel = static function (string $statusValue, int $availableUnits): string {
        $normalized = strtolower(trim($statusValue));

        return match ($normalized) {
            'maintenance' => 'Maintenance',
            'unavailable' => 'Tidak Tersedia',
            'ready' => $availableUnits > 0 ? 'Tersedia' : 'Penuh / Sedang Disewa',
            default => $availableUnits > 0 ? 'Tersedia' : 'Tidak Tersedia',
        };
    };
    $resolveStatusClasses = static function (string $statusValue, int $availableUnits): string {
        $normalized = strtolower(trim($statusValue));

        return match ($normalized) {
            'maintenance' => 'border-amber-500/20 bg-amber-950/75 text-amber-300',
            'unavailable' => 'border-rose-400/30 bg-rose-950/75 text-rose-300',
            'ready' => $availableUnits > 0
                ? 'border-emerald-400/30 bg-emerald-950/75 text-emerald-300'
                : 'border-amber-400/30 bg-amber-950/75 text-amber-200',
            default => $availableUnits > 0
                ? 'border-emerald-400/30 bg-emerald-950/75 text-emerald-300'
                : 'border-rose-400/30 bg-rose-950/75 text-rose-300',
        };
    };
    $resolveEquipmentImage = static function ($equipment, string $fallbackCategory = '') use ($fallbackImageByCategory) {
        $imagePath = data_get($equipment, 'image_path') ?: data_get($equipment, 'image');
        $imageUrl = site_media_url($imagePath);

        if ($imageUrl) {
            return $imageUrl;
        }

        $categoryName = trim((string) data_get($equipment, 'category.name', $fallbackCategory));
        $normalizedCategory = strtolower(trim($categoryName));

        foreach ($fallbackImageByCategory as $needle => $fallback) {
            if (str_contains($normalizedCategory, $needle)) {
                return $fallback;
            }
        }

        return site_asset('MANAKE-FAV-M.png');
    };
    $realEquipmentItems = collect($productsReady ?? [])
        ->filter()
        ->values()
        ->map(static function ($equipment) use ($resolveEquipmentImage, $resolveStatusLabel, $resolveStatusClasses) {
            $statusValue = (string) data_get($equipment, 'status', 'ready');
            $stock = (int) data_get($equipment, 'stock', 0);
            $availableUnits = (int) data_get($equipment, 'available_units', $stock);
            $reservedUnits = (int) data_get($equipment, 'reserved_units', max($stock - $availableUnits, 0));
            $categoryName = (string) data_get($equipment, 'category.name', 'Peralatan');

            return [
                'name' => (string) data_get($equipment, 'name', 'Equipment'),
                'slug' => (string) data_get($equipment, 'slug', ''),
                'category' => $categoryName,
                'status' => $statusValue,
                'status_label' => $resolveStatusLabel($statusValue, $availableUnits),
                'status_class' => $resolveStatusClasses($statusValue, $availableUnits),
                'available_units' => $availableUnits,
                'reserved_units' => $reservedUnits,
                'stock' => $stock,
                'price' => (int) data_get($equipment, 'price_per_day', 0),
                'image' => $resolveEquipmentImage($equipment, $categoryName),
                'url' => route('product.show', (string) data_get($equipment, 'slug', '')),
            ];
        });
    $equipmentItems = $realEquipmentItems->isNotEmpty() ? $realEquipmentItems : $fallbackEquipmentCards;
    $marqueeCategories = collect($navCategories ?? [])
        ->map(fn ($category) => trim((string) data_get($category, 'name', '')))
        ->filter()
        ->values();
    if ($marqueeCategories->isEmpty()) {
        $marqueeCategories = $fallbackMarqueeCategories;
    }
    $marqueeCategories = $marqueeCategories->concat($marqueeCategories)->take(max(12, $marqueeCategories->count() * 2))->values();
    $guestRentalSnapshotItems = collect($guestRentalSnapshot ?? [])->filter();
    $homeStats = $homeRentalStats ?? [];
    $snapshotNumbers = [
        'rented_today' => (int) data_get($homeStats, 'rented_today', 0),
        'available_items' => (int) data_get($homeStats, 'available_items', $realEquipmentItems->count()),
        'upcoming_bookings' => (int) data_get($homeStats, 'upcoming_bookings', 0),
    ];
    $rentalTimelineItems = $guestRentalSnapshotItems
        ->map(function ($item) {
            $start = data_get($item, 'start_date');
            $end = data_get($item, 'end_date');

            return [
                'name' => (string) data_get($item, 'name', 'Equipment'),
                'qty' => (int) data_get($item, 'qty', 1),
                'period' => $start && $end
                    ? \Carbon\Carbon::parse((string) $start)->translatedFormat('d M') . ' - ' . \Carbon\Carbon::parse((string) $end)->translatedFormat('d M Y')
                    : 'Jadwal belum dikunci',
            ];
        })
        ->values();
    $carouselItems = $equipmentItems->concat($equipmentItems)->take(max(12, $equipmentItems->count() * 2))->values();
@endphp

@section('content')
    <div class="bg-[#0A0A0B] text-[#E8E8EC]">
        <section class="relative min-h-[100svh] overflow-hidden">
            <div class="absolute inset-0">
                <img src="{{ site_asset('images/hero-bg.jpg') }}" alt="Set produksi film profesional" class="h-full w-full object-cover object-center" />
                <div class="absolute inset-0 bg-gradient-to-b from-black/35 via-[#0A0A0B]/45 to-[#0A0A0B]"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#0A0A0B]/82 via-transparent to-transparent"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_40%,rgba(255,255,255,0.05),transparent_22%),radial-gradient(circle_at_15%_20%,rgba(212,168,67,0.12),transparent_20%),radial-gradient(circle_at_85%_16%,rgba(255,255,255,0.05),transparent_18%)]"></div>
            </div>

            <div class="relative mx-auto grid min-h-[100svh] max-w-7xl items-center gap-12 px-6 pb-14 pt-28 md:px-10 lg:grid-cols-[1.08fr_0.92fr]">
                <div class="max-w-3xl">
                    <h1 class="text-[clamp(3.1rem,8vw,7rem)] font-semibold leading-[0.93] tracking-[-0.055em] text-[#E8E8EC]" style="font-family: 'DM Serif Display', Georgia, serif;">
                        <span class="block">Sewa</span>
                        <span
                            x-data="{
                                words: @js($heroCategories),
                                index: 0,
                                init() {
                                    if (!this.words.length) return;
                                    this.timer = window.setInterval(() => {
                                        this.index = (this.index + 1) % this.words.length;
                                    }, 2400);
                                }
                            }"
                            class="block"
                        >
                            <span class="relative mr-3 inline-grid min-w-[5.4em] overflow-hidden align-baseline text-[#D4A843]">
                                <template x-for="(word, wordIndex) in words" :key="word">
                                    <span
                                        x-show="index === wordIndex"
                                        x-transition:enter="transition duration-700 ease-out"
                                        x-transition:enter-start="translate-y-full opacity-0 blur-sm"
                                        x-transition:enter-end="translate-y-0 opacity-100 blur-0"
                                        x-transition:leave="transition duration-500 ease-in absolute"
                                        x-transition:leave-start="translate-y-0 opacity-100 blur-0"
                                        x-transition:leave-end="-translate-y-full opacity-0 blur-sm"
                                        class="col-start-1 row-start-1 inline-block will-change-transform"
                                        x-text="word"
                                    ></span>
                                </template>
                            </span>
                            <span class="inline-block">terbaik, kapan saja.</span>
                        </span>
                    </h1>

                    <p class="mt-7 max-w-2xl text-lg leading-8 text-[#A0A0A8] md:text-xl">
                        Manake menyediakan akses ke kamera sinema, lighting profesional, perangkat audio, drone, stabilizer, dan lainnya — siap diambil dan digunakan. Tanpa kerumitan kepemilikan, hasil tetap profesional.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <a href="#equipment" class="inline-flex items-center gap-3 rounded-md bg-[#D4A843] px-7 py-4 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                            Lihat Peralatan
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4 backdrop-blur-md md:p-5">
                        <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Live Snapshot</p>
                                <h2 class="mt-1 text-2xl font-semibold text-[#E8E8EC]">Ringkasan rental hari ini</h2>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                ['label' => 'Disewa hari ini', 'value' => number_format($snapshotNumbers['rented_today']), 'tone' => 'text-[#D4A843]'],
                                ['label' => 'Item tersedia', 'value' => number_format($snapshotNumbers['available_items']), 'tone' => 'text-emerald-300'],
                                ['label' => 'Booking berikutnya', 'value' => number_format($snapshotNumbers['upcoming_bookings']), 'tone' => 'text-sky-300'],
                            ] as $tile)
                                <div class="rounded-2xl border border-white/10 bg-black/30 p-4">
                                    <p class="text-xs uppercase tracking-[0.22em] text-[#A0A0A8]">{{ $tile['label'] }}</p>
                                    <p class="mt-3 text-3xl font-black {{ $tile['tone'] }}">{{ $tile['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/10 bg-gradient-to-br from-[#111113]/95 via-[#0A0A0B]/90 to-[#111113]/95 p-4 md:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Jadwal Rental</p>
                                <p class="mt-1 text-sm text-[#A0A0A8]">Sinkron dari booking aktif di database</p>
                            </div>
                            <span class="rounded-full border border-emerald-400/20 bg-emerald-950/40 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-300">Live</span>
                        </div>
                        <div class="mt-4 grid gap-2">
                            @forelse ($rentalTimelineItems->take(3) as $rental)
                                <div class="grid grid-cols-[1fr_auto] gap-3 rounded-xl border border-white/10 bg-black/25 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-[#E8E8EC]">{{ $rental['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-[#A0A0A8]">{{ $rental['qty'] }} unit disewa</p>
                                    </div>
                                    <p class="self-center text-right text-xs font-semibold text-[#D4A843]">{{ $rental['period'] }}</p>
                                </div>
                            @empty
                                <div class="rounded-xl border border-white/10 bg-black/25 px-3 py-3">
                                    <p class="text-sm font-semibold text-[#E8E8EC]">Belum ada booking aktif</p>
                                    <p class="mt-1 text-xs text-[#A0A0A8]">Semua alat siap dicek dari katalog live.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="categories" class="border-y border-[#1A1A1E] bg-[#111113] py-16 md:py-20">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#0A0A0B] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.9)] md:p-7">
                    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Kategori & Jadwal</p>
                            <h2 class="mt-3 text-3xl leading-tight text-[#E8E8EC] md:text-4xl" style="font-family: 'DM Serif Display', Georgia, serif;">
                                Lihat kategori alat dan booking aktif dalam satu tempat.
                            </h2>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach ($marqueeCategories->unique()->take(8) as $category)
                                    <a href="{{ route('catalog', ['category' => \Illuminate\Support\Str::slug($category)]) }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                        {{ $category }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @forelse ($rentalTimelineItems->take(5) as $rental)
                                <div class="grid gap-3 rounded-xl border border-white/10 bg-[#111113] p-4 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold text-[#E8E8EC]">{{ $rental['name'] }}</p>
                                        <p class="mt-1 text-sm text-[#A0A0A8]">{{ $rental['qty'] }} unit sedang masuk jadwal rental</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[#D4A843]">{{ $rental['period'] }}</p>
                                </div>
                            @empty
                                <div class="rounded-xl border border-white/10 bg-[#111113] p-5">
                                    <p class="text-base font-semibold text-[#E8E8EC]">Belum ada booking aktif</p>
                                    <p class="mt-2 text-sm leading-6 text-[#A0A0A8]">Data live dari database menunjukkan slot rental aktif masih kosong.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="equipment" class="bg-[#0A0A0B] py-24 md:py-28">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div class="mb-12 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Peralatan Unggulan</p>
                        <h2 class="text-[clamp(2.5rem,4vw,4rem)] leading-[0.96] tracking-[-0.04em] text-[#E8E8EC]" style="font-family: 'DM Serif Display', Georgia, serif;">
                            Tersedia untuk disewa hari ini.
                        </h2>
                    </div>
                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#A0A0A8] transition hover:text-[#D4A843]">
                        Lihat semua peralatan yang tersedia <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="group/carousel overflow-hidden pb-3">
                    <div class="flex w-max gap-5 animate-[manake_equipment_carousel_42s_linear_infinite] group-hover/carousel:[animation-play-state:paused]">
                        @foreach ($carouselItems as $item)
                            @php
                                $isAvailable = (bool) data_get($item, 'available', data_get($item, 'available_units', 0) > 0);
                                $itemUrl = data_get($item, 'url', route('catalog'));
                                $itemName = (string) data_get($item, 'name', 'Equipment');
                                $itemCategory = (string) data_get($item, 'category', 'Peralatan');
                                $itemPrice = (int) data_get($item, 'price', data_get($item, 'price_per_day', 0));
                                $itemImage = (string) data_get($item, 'image', site_asset('MANAKE-FAV-M.png'));
                                $itemStatusValue = (string) data_get($item, 'status', ($isAvailable ? 'ready' : 'unavailable'));
                                $itemAvailableUnits = (int) data_get($item, 'available_units', $isAvailable ? 1 : 0);
                                $itemStatusLabel = (string) data_get($item, 'status_label', $resolveStatusLabel($itemStatusValue, $itemAvailableUnits));
                                $itemStatusClass = (string) data_get($item, 'status_class', $resolveStatusClasses($itemStatusValue, $itemAvailableUnits));
                            @endphp
                            <article class="group w-[285px] overflow-hidden rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] shadow-[0_18px_50px_-28px_rgba(0,0,0,0.8)]">
                                <div class="relative aspect-[4/3] overflow-hidden bg-[#111113]">
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemName }}"
                                        class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.03]"
                                        onerror="this.onerror=null;this.src='{{ site_asset('MANAKE-FAV-M.png') }}';"
                                        loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $loop->index === 0 ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                    <div class="absolute left-3 top-3 rounded-sm border border-white/10 bg-[#0A0A0B]/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#E8E8EC]">
                                        {{ $itemCategory }}
                                    </div>
                                    <div class="absolute right-3 top-3 rounded-sm border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $itemStatusClass }}">
                                        {{ $itemStatusLabel }}
                                    </div>
                                    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/60 to-transparent"></div>
                                </div>
                                <div class="flex flex-1 flex-col gap-3 p-5">
                                    <div>
                                        <h3 class="text-base font-semibold leading-snug text-[#E8E8EC]">{{ $itemName }}</h3>
                                        <p class="mt-1 text-xs text-[#A0A0A8]">{{ $itemCategory }}</p>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/8 bg-black/20 px-3 py-2.5">
                                        <div>
                                            <p class="text-[10px] uppercase tracking-[0.22em] text-[#A0A0A8]">Harga sewa</p>
                                            <p class="mt-1 text-sm font-semibold text-[#E8E8EC]">
                                                Rp {{ number_format($itemPrice, 0, ',', '.') }} <span class="text-[#A0A0A8]">/ hari</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] uppercase tracking-[0.22em] text-[#A0A0A8]">Status</p>
                                            <p class="mt-1 text-sm font-semibold {{ $isAvailable ? 'text-emerald-300' : 'text-amber-200' }}">
                                                {{ $isAvailable ? 'Available' : 'Booked' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="{{ $itemUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-3 text-xs font-semibold {{ $isAvailable ? 'bg-[#D4A843] text-[#0A0A0B] transition hover:bg-[#e0ba5d]' : 'cursor-pointer bg-[#1A1A1E] text-[#A0A0A8] transition hover:text-[#E8E8EC]' }}">
                                            {{ $isAvailable ? 'Lihat Detail & Booking' : 'Lihat Detail' }}
                                            @if($isAvailable)
                                                <span aria-hidden="true">→</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="border-y border-[#1A1A1E] bg-[#111113] py-24 md:py-28">
            <div class="mx-auto grid max-w-7xl gap-14 px-6 md:px-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Tentang Manake</p>
                    <h2 class="max-w-xl text-[clamp(2.6rem,4vw,4rem)] leading-[0.96] tracking-[-0.04em] text-[#E8E8EC]" style="font-family: 'DM Serif Display', Georgia, serif;">
                        Peralatan produksi yang Anda butuhkan, tepat saat Anda membutuhkannya.
                    </h2>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-[#A0A0A8]">
                        Manake adalah platform rental peralatan media profesional yang dibangun untuk sutradara, sinematografer, kreator konten, dan rumah produksi. Kami menghadirkan hanya gear terbaik berkelas sinema — mulai dari kamera cinema, lensa broadcast, audio profesional, drone, hingga stabilizer.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['title' => 'Peralatan Terverifikasi', 'body' => 'Setiap item diuji, dikalibrasi, dan dirawat sebelum setiap penyewaan. Tidak ada kejutan di lokasi syuting.'],
                        ['title' => 'Pengambilan & Pengiriman Fleksibel', 'body' => 'Ambil di studio kami atau minta peralatan dikirim ke lokasi Anda. Tersedia same-day untuk produksi lokal.'],
                        ['title' => 'Terlindungi & Diasuransikan', 'body' => 'Semua penyewaan dilengkapi opsi perlindungan kerusakan agar produksi Anda tetap berjalan sesuai jadwal.'],
                        ['title' => 'Dukungan di Lokasi Syuting', 'body' => 'Tim operator berpengalaman kami siap memberikan dukungan teknis sepanjang hari syuting Anda.'],
                    ] as $pillar)
                        <div class="rounded-lg border border-[#1A1A1E] bg-[#0A0A0B] p-6">
                            <h3 class="text-sm font-semibold text-[#E8E8EC]">{{ $pillar['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-[#A0A0A8]">{{ $pillar['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="cara-sewa" class="bg-[#0A0A0B] py-24 md:py-28">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div class="max-w-2xl">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">Cara Sewa</p>
                    <h2 class="text-[clamp(2.4rem,4vw,3.8rem)] leading-[0.96] tracking-[-0.04em] text-[#E8E8EC]" style="font-family: 'DM Serif Display', Georgia, serif;">
                        Sewa peralatan dalam empat langkah mudah.
                    </h2>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['number' => '01', 'title' => 'Pilih Peralatan', 'body' => 'Telusuri katalog kami dan pilih peralatan yang sesuai dengan kebutuhan produksi Anda. Filter berdasarkan kategori, ketersediaan, dan harga.'],
                        ['number' => '02', 'title' => 'Ajukan Pemesanan', 'body' => 'Isi formulir pemesanan dengan tanggal sewa, durasi, dan detail produksi Anda. Kami akan mengkonfirmasi dalam waktu 1x24 jam.'],
                        ['number' => '03', 'title' => 'Ambil atau Terima', 'body' => 'Ambil peralatan langsung di studio kami di Jakarta, atau pilih layanan pengiriman ke lokasi produksi Anda.'],
                        ['number' => '04', 'title' => 'Shoot & Kembalikan', 'body' => 'Gunakan peralatan untuk produksi Anda, lalu kembalikan sesuai jadwal. Tim kami siap membantu jika ada kendala teknis.'],
                    ] as $step)
                        <article class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold tracking-[0.24em] text-[#D4A843]">{{ $step['number'] }}</span>
                                <span class="text-xs font-medium text-[#A0A0A8]">›</span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-[#E8E8EC]">{{ $step['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-[#A0A0A8]">{{ $step['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @include('partials.footer')
    </div>
@endsection

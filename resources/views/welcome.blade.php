@extends('layouts.landing')

@section('title', 'Manake — Sewa Alat Produksi')

@php
    $heroCategories = ['Kamera', 'Lighting', 'Audio', 'Drone', 'Stabilizer', 'HT'];
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
            'unavailable' => app()->getLocale() === 'en' ? 'Unavailable' : 'Tidak Tersedia',
            'ready' => $availableUnits > 0 
                ? (app()->getLocale() === 'en' ? 'Available' : 'Tersedia') 
                : (app()->getLocale() === 'en' ? 'Fully Booked' : 'Penuh / Sedang Disewa'),
            default => $availableUnits > 0 
                ? (app()->getLocale() === 'en' ? 'Available' : 'Tersedia') 
                : (app()->getLocale() === 'en' ? 'Unavailable' : 'Tidak Tersedia'),
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
    $equipmentItems = $realEquipmentItems;
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
                    ? \Carbon\Carbon::parse((string) $start)->locale(app()->getLocale())->translatedFormat('d M').' - '.\Carbon\Carbon::parse((string) $end)->locale(app()->getLocale())->translatedFormat('d M Y')
                    : (app()->getLocale() === 'en' ? 'Schedule not locked' : 'Jadwal belum dikunci'),
            ];
        })
        ->values();
    $carouselItems = $equipmentItems->isNotEmpty()
        ? $equipmentItems->concat($equipmentItems)->take(max(12, $equipmentItems->count() * 2))->values()
        : collect();
    $heroDescriptionText = __('app.home.hero_subtitle');
@endphp

@section('content')
    <div class="bg-[#0A0A0B] text-[#E8E8EC] transition-colors duration-200">
        @php
            $heroCategories = app()->getLocale() === 'en' 
                ? ['Camera', 'Lighting', 'Audio', 'Drone', 'Stabilizer', 'Walkie-Talkie'] 
                : ['Kamera', 'Lighting', 'Audio', 'Drone', 'Stabilizer', 'HT'];
        @endphp
        <style>
            /* Scoped Theme-Aware Hero Styles */
            .manake-hero-island {
                transition: background-color 0.25s ease-in-out;
            }
            
            /* Dark Mode Defaults */
            html[data-theme-resolved="dark"] .hero-title-text {
                font-family: 'DM Serif Display', Georgia, serif !important;
                color: #E8E8EC !important;
                letter-spacing: -0.05em !important;
            }
            html[data-theme-resolved="dark"] .hero-rotating-word {
                color: #D4A843 !important;
            }
            html[data-theme-resolved="dark"] .hero-desc-text {
                color: #A0A0A8 !important;
            }
            html[data-theme-resolved="dark"] .hero-card-glass {
                border-color: rgba(255, 255, 255, 0.10) !important;
                background-color: rgba(255, 255, 255, 0.05) !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .hero-card-solid {
                border-color: rgba(255, 255, 255, 0.10) !important;
                background: linear-gradient(to bottom right, rgba(17, 17, 19, 0.95), rgba(10, 10, 11, 0.90)) !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .hero-card-title {
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .hero-card-kicker {
                color: #D4A843 !important;
            }
            html[data-theme-resolved="dark"] .hero-tile {
                background-color: rgba(0, 0, 0, 0.35) !important;
                border-color: rgba(255, 255, 255, 0.10) !important;
            }
            html[data-theme-resolved="dark"] .hero-tile-label {
                color: #A0A0A8 !important;
            }
            html[data-theme-resolved="dark"] .hero-tile-val-1 { color: #D4A843 !important; }
            html[data-theme-resolved="dark"] .hero-tile-val-2 { color: #34D399 !important; }
            html[data-theme-resolved="dark"] .hero-tile-val-3 { color: #7DD3FC !important; }
            html[data-theme-resolved="dark"] .hero-schedule-item {
                background-color: rgba(0, 0, 0, 0.35) !important;
                border-color: rgba(255, 255, 255, 0.10) !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .hero-schedule-item .rented-count {
                color: #A0A0A8 !important;
            }
            html[data-theme-resolved="dark"] .hero-schedule-item .period-text {
                color: #D4A843 !important;
            }

            /* Light Mode Overrides */
            html[data-theme-resolved="light"] .manake-hero-island .hero-title-text {
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif !important;
                font-weight: 800 !important;
                letter-spacing: -0.04em !important;
                color: #F8FAFC !important;
                text-shadow: 0 3px 18px rgba(0,0,0,0.55), 0 14px 38px rgba(0,0,0,0.35) !important;
            }
            html[data-theme-resolved="light"] .manake-hero-island .hero-title-text span:not(.hero-rotating-word):not(.hero-rotating-word *),
            html[data-theme-resolved="light"] .manake-hero-island .hero-title-text .inline-block {
                color: #F8FAFC !important;
            }
            html[data-theme-resolved="light"] .manake-hero-island .hero-rotating-word,
            html[data-theme-resolved="light"] .manake-hero-island .hero-rotating-word span {
                color: #3B82F6 !important;
                text-shadow: 0 2px 10px rgba(0,0,0,0.35), 0 10px 28px rgba(0,0,0,0.28) !important;
            }
            html[data-theme-resolved="light"] .manake-hero-island .hero-desc-text {
                color: rgba(248, 250, 252, 0.92) !important;
                text-shadow: 0 2px 12px rgba(0,0,0,0.45) !important;
            }
            html[data-theme-resolved="light"] .hero-card-glass {
                border-color: rgba(37, 99, 235, 0.15) !important;
                background-color: rgba(255, 255, 255, 0.85) !important;
                color: #111827 !important;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06) !important;
            }
            html[data-theme-resolved="light"] .hero-card-solid {
                border-color: rgba(37, 99, 235, 0.15) !important;
                background-color: rgba(255, 255, 255, 0.90) !important;
                color: #111827 !important;
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06) !important;
            }
            html[data-theme-resolved="light"] .hero-card-title {
                color: #111827 !important;
            }
            html[data-theme-resolved="light"] .hero-card-kicker {
                color: #2563EB !important; /* Manake Blue */
            }
            html[data-theme-resolved="light"] .hero-tile {
                background-color: rgba(37, 99, 235, 0.04) !important;
                border-color: rgba(37, 99, 235, 0.10) !important;
            }
            html[data-theme-resolved="light"] .hero-tile-label {
                color: #6B7280 !important;
            }
            html[data-theme-resolved="light"] .hero-tile-val-1 { color: #2563EB !important; }
            html[data-theme-resolved="light"] .hero-tile-val-2 { color: #059669 !important; }
            html[data-theme-resolved="light"] .hero-tile-val-3 { color: #0284C7 !important; }
            html[data-theme-resolved="light"] .hero-schedule-item {
                background-color: rgba(37, 99, 235, 0.04) !important;
                border-color: rgba(37, 99, 235, 0.10) !important;
                color: #111827 !important;
            }
            html[data-theme-resolved="light"] .hero-schedule-item .rented-count {
                color: #4B5563 !important;
            }
            html[data-theme-resolved="light"] .hero-schedule-item .period-text {
                color: #2563EB !important;
            }

            /* Scoped Theme-Aware Core Classes for Landing Page */
            .home-kicker {
                font-size: 0.75rem; /* text-xs */
                font-weight: 600; /* font-semibold */
                text-transform: uppercase;
                letter-spacing: 0.28em;
                transition: color 0.25s ease-in-out;
            }
            .home-heading {
                font-size: clamp(2.4rem, 4vw, 4rem);
                line-height: 0.96;
                letter-spacing: -0.04em;
                transition: font-family 0.25s ease-in-out, color 0.25s ease-in-out;
            }
            .home-copy {
                font-size: 1.125rem; /* text-lg */
                line-height: 2rem; /* leading-8 */
                transition: color 0.25s ease-in-out;
            }
            .home-card {
                border-radius: 0.5rem; /* rounded-lg */
                border-width: 1px;
                padding: 1.5rem; /* p-6 */
                transition: background-color 0.25s ease-in-out, border-color 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
            }
            .home-carousel-card {
                display: flex;
                min-height: 33rem;
                width: 82vw;
                max-width: 24rem;
                flex-shrink: 0;
                scroll-snap-align: start;
                overflow: hidden;
                border-radius: 1.35rem;
                border-width: 1px;
                transition: background-color 0.25s ease-in-out, border-color 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
            }
            @media (min-width: 640px) {
                .home-carousel-card { width: 21rem; }
            }
            @media (min-width: 1024px) {
                .home-carousel-card { width: 23rem; }
            }
            
            .home-primary-button {
                display: inline-flex;
                width: 100%;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border-radius: 0.375rem; /* rounded-md */
                padding: 0.75rem 1rem; /* py-3 px-4 */
                font-size: 0.75rem; /* text-xs */
                font-weight: 600; /* font-semibold */
                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            }
            .home-secondary-button {
                display: inline-flex;
                height: 2.75rem; /* h-11 */
                width: 2.75rem; /* w-11 */
                align-items: center;
                justify-content: center;
                border-radius: 9999px; /* rounded-full */
                border-width: 1px;
                font-size: 1.25rem; /* text-xl */
                font-weight: 600; /* font-semibold */
                transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, color 0.2s ease-in-out;
            }

            /* DARK MODE (Master/Cinematic) */
            html[data-theme-resolved="dark"] .home-kicker {
                color: #D4A843 !important; /* Gold */
            }
            html[data-theme-resolved="dark"] .home-heading {
                font-family: 'DM Serif Display', Georgia, serif !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .home-copy {
                color: #A0A0A8 !important;
            }
            html[data-theme-resolved="dark"] .home-card {
                background-color: #0A0A0B !important;
                border-color: #1A1A1E !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .home-carousel-card {
                background-color: #111113 !important;
                border-color: #1A1A1E !important;
                box-shadow: 0 18px 50px -28px rgba(0,0,0,0.8) !important;
            }
            html[data-theme-resolved="dark"] .home-carousel-card-img-bg {
                background-color: #111113 !important;
            }
            html[data-theme-resolved="dark"] .home-carousel-card-meta {
                border-color: rgba(255, 255, 255, 0.08) !important;
                background-color: rgba(0, 0, 0, 0.2) !important;
            }
            html[data-theme-resolved="dark"] .home-primary-button.btn-available {
                background-color: #D4A843 !important;
                color: #0A0A0B !important;
            }
            html[data-theme-resolved="dark"] .home-primary-button.btn-available:hover {
                background-color: #e0ba5d !important;
            }
            html[data-theme-resolved="dark"] .home-primary-button.btn-booked {
                background-color: #1A1A1E !important;
                color: #A0A0A8 !important;
            }
            html[data-theme-resolved="dark"] .home-primary-button.btn-booked:hover {
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .home-secondary-button.btn-outline {
                border-color: rgba(255, 255, 255, 0.1) !important;
                background-color: #111113 !important;
                color: #E8E8EC !important;
            }
            html[data-theme-resolved="dark"] .home-secondary-button.btn-outline:hover {
                border-color: rgba(212, 168, 67, 0.5) !important;
                color: #D4A843 !important;
            }
            html[data-theme-resolved="dark"] .home-secondary-button.btn-solid {
                border-color: transparent !important;
                background-color: #D4A843 !important;
                color: #0A0A0B !important;
            }
            html[data-theme-resolved="dark"] .home-secondary-button.btn-solid:hover {
                background-color: #e0ba5d !important;
            }
            html[data-theme-resolved="dark"] .home-card-number {
                color: #D4A843 !important;
            }
            html[data-theme-resolved="dark"] .home-badge-category {
                border-color: rgba(255, 255, 255, 0.1) !important;
                background-color: rgba(10, 10, 11, 0.8) !important;
                color: #E8E8EC !important;
            }

            /* LIGHT MODE (Clean/Blue) */
            html[data-theme-resolved="light"] .home-kicker {
                color: #2563EB !important; /* Blue */
            }
            html[data-theme-resolved="light"] .home-heading {
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif !important;
                font-weight: 800 !important;
                color: #111827 !important;
                letter-spacing: -0.04em !important;
            }
            html[data-theme-resolved="light"] .home-copy {
                color: #4B5563 !important;
            }
            html[data-theme-resolved="light"] .home-card {
                background-color: #FFFFFF !important;
                border-color: #E5E2DA !important;
                color: #111827 !important;
                box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03) !important;
            }
            html[data-theme-resolved="light"] .home-carousel-card {
                background-color: #FFFFFF !important;
                border-color: #E5E2DA !important;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04) !important;
            }
            html[data-theme-resolved="light"] .home-carousel-card-img-bg {
                background-color: #F9FAFB !important; /* Warm light gray background for product images */
            }
            html[data-theme-resolved="light"] .home-carousel-card-meta {
                border-color: #F3F4F6 !important;
                background-color: #F9FAFB !important;
            }
            html[data-theme-resolved="light"] .home-primary-button.btn-available {
                background-color: #2563EB !important; /* Blue */
                color: #FFFFFF !important;
            }
            html[data-theme-resolved="light"] .home-primary-button.btn-available:hover {
                background-color: #1d4ed8 !important;
            }
            html[data-theme-resolved="light"] .home-primary-button.btn-booked {
                background-color: #F3F4F6 !important;
                color: #4B5563 !important;
            }
            html[data-theme-resolved="light"] .home-primary-button.btn-booked:hover {
                color: #111827 !important;
            }
            html[data-theme-resolved="light"] .home-secondary-button.btn-outline {
                border-color: #E5E2DA !important;
                background-color: #FFFFFF !important;
                color: #4B5563 !important;
            }
            html[data-theme-resolved="light"] .home-secondary-button.btn-outline:hover {
                border-color: rgba(37, 99, 235, 0.5) !important;
                color: #2563EB !important;
            }
            html[data-theme-resolved="light"] .home-secondary-button.btn-solid {
                border-color: transparent !important;
                background-color: #2563EB !important;
                color: #FFFFFF !important;
            }
            html[data-theme-resolved="light"] .home-secondary-button.btn-solid:hover {
                background-color: #1d4ed8 !important;
            }
            html[data-theme-resolved="light"] .home-card-number {
                color: #2563EB !important;
            }
            html[data-theme-resolved="light"] .home-badge-category {
                border-color: rgba(37, 99, 235, 0.15) !important;
                background-color: rgba(37, 99, 235, 0.05) !important;
                color: #2563EB !important;
            }
            html[data-theme-resolved="light"] .card-img-overlay {
                display: none !important;
            }
        </style>
        <section class="manake-hero-island relative min-h-[calc(100svh-8rem)] overflow-hidden" data-theme-island="dark">
            <div class="absolute inset-0">
                <!-- Dark mode background image & overlay -->
                <div class="absolute inset-0 hidden dark:block">
                    <img src="{{ site_asset('images/hero-bg.jpg') }}" alt="Set produksi film profesional" class="h-full w-full object-cover object-center" />
                    <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-[#0A0A0B]/55 to-[#0A0A0B]"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#0A0A0B]/90 via-transparent to-transparent"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_40%,rgba(255,255,255,0.05),transparent_22%),radial-gradient(circle_at_15%_20%,rgba(212,168,67,0.12),transparent_20%),radial-gradient(circle_at_85%_16%,rgba(255,255,255,0.05),transparent_18%)]"></div>
                </div>
                <!-- Light mode background image & overlay -->
                <div class="absolute inset-0 block dark:hidden">
                    <img src="{{ site_asset('images/hero-bg-light.jpg') }}" alt="Set produksi film profesional" class="h-full w-full object-cover object-center" />
                    <!-- Left gradient: high-contrast dark overlay to ensure readable off-white text -->
                    <div class="absolute inset-0 bg-gradient-to-r from-[rgba(5,8,15,0.72)] via-[rgba(5,8,15,0.36)] to-transparent"></div>
                    <!-- Bottom gradient: blends seamlessly into the rest of the light page background -->
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#F7F7F4]/20 to-[#F7F7F4]"></div>
                    <!-- Subtle blue decorative lighting glow that echoes the blue accent -->
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(37,99,235,0.08),transparent_25%),radial-gradient(circle_at_85%_16%,rgba(37,99,235,0.04),transparent_20%)]"></div>
                </div>
            </div>

            <div class="relative mx-auto grid min-h-[calc(100svh-8rem)] max-w-7xl items-center gap-12 px-6 pb-12 pt-24 md:px-10 lg:grid-cols-[1.08fr_0.92fr]">
                <div class="max-w-3xl">
                    <h1 class="hero-title-text text-[clamp(2.5rem,5.2vw,4.5rem)] font-semibold leading-[0.98] tracking-[-0.055em] text-[#E8E8EC]">
                        <span class="block">{{ __('app.home.hero_kicker') }}</span>
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
                            <span class="relative mr-3 inline-grid min-w-[5.4em] overflow-hidden align-baseline hero-rotating-word">
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
                            <span class="inline-block">{{ __('app.home.hero_title') }}</span>
                        </span>
                    </h1>

                    <p class="mt-7 max-w-2xl text-base leading-8 hero-desc-text md:text-lg">
                        {{ $heroDescriptionText }}
                    </p>

                    <div class="mt-10"></div>
                </div>

                <div class="grid gap-4">
                    <div class="rounded-[1.5rem] border p-4 backdrop-blur-md md:p-5 hero-card-glass">
                        <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] hero-card-kicker">{{ __('app.home.snapshot_kicker') }}</p>
                                <h2 class="mt-1 text-2xl font-semibold hero-card-title">{{ __('app.home.snapshot_title') }}</h2>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            @foreach ([
                                ['label' => __('app.home.snapshot_rented'), 'value' => number_format($snapshotNumbers['rented_today']), 'tone' => 'hero-tile-val-1'],
                                ['label' => __('app.home.snapshot_available'), 'value' => number_format($snapshotNumbers['available_items']), 'tone' => 'hero-tile-val-2'],
                                ['label' => __('app.home.snapshot_upcoming'), 'value' => number_format($snapshotNumbers['upcoming_bookings']), 'tone' => 'hero-tile-val-3'],
                            ] as $tile)
                                <div class="rounded-2xl border p-4 hero-tile">
                                    <p class="text-[10px] uppercase tracking-[0.22em] hero-tile-label">{{ $tile['label'] }}</p>
                                    <p class="mt-3 text-2xl font-black {{ $tile['tone'] }}">{{ $tile['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border p-4 md:p-5 backdrop-blur-md hero-card-solid">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] hero-card-kicker">{{ __('app.home.schedule_kicker') }}</p>
                                <p class="mt-1 text-xs hero-tile-label">{{ __('app.home.schedule_subtitle') }}</p>
                            </div>
                            <span class="rounded-full border border-emerald-400/20 bg-emerald-950/40 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-300">{{ __('app.home.live_badge') }}</span>
                        </div>
                        <div class="mt-4 grid gap-2">
                            @forelse ($rentalTimelineItems->take(3) as $rental)
                                <div class="grid grid-cols-[1fr_auto] gap-3 rounded-xl border px-3 py-2.5 hero-schedule-item">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold hero-card-title">{{ $rental['name'] }}</p>
                                        <p class="mt-0.5 text-xs rented-count">{{ $rental['qty'] }} {{ $rental['qty'] > 1 ? __('app.home.units_rented') : __('app.home.unit_rented') }}</p>
                                    </div>
                                    <p class="self-center text-right text-xs font-semibold period-text">{{ $rental['period'] }}</p>
                                </div>
                            @empty
                                <div class="rounded-xl border px-3 py-3 hero-schedule-item">
                                    <p class="text-sm font-semibold hero-card-title">{{ __('app.home.empty_bookings') ?? 'Belum ada booking aktif' }}</p>
                                    <p class="mt-1 text-xs rented-count">{{ __('app.home.empty_bookings_desc') ?? 'Semua alat siap dicek dari katalog live.' }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="equipment" class="bg-[#0A0A0B] py-24 md:py-28">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div
                    class="mb-12 flex flex-col gap-5 md:flex-row md:items-end md:justify-between"
                    x-data="{
                        interval: null,
                        scroll(direction) {
                            const track = document.getElementById('featured-equipment-track');
                            if (!track) return;
                            const card = track.querySelector('[data-carousel-card]');
                            const amount = card ? card.getBoundingClientRect().width + 24 : track.clientWidth * 0.85;
                            const maxScrollLeft = track.scrollWidth - track.clientWidth;
                            if (direction === 1 && track.scrollLeft >= maxScrollLeft - 5) {
                                track.scrollTo({ left: 0, behavior: 'smooth' });
                            } else {
                                track.scrollBy({ left: direction * amount, behavior: 'smooth' });
                            }
                            this.resetTimer();
                        },
                        init() {
                            this.startTimer();
                        },
                        startTimer() {
                            this.interval = window.setInterval(() => {
                                const track = document.getElementById('featured-equipment-track');
                                if (!track) return;
                                const card = track.querySelector('[data-carousel-card]');
                                const amount = card ? card.getBoundingClientRect().width + 24 : track.clientWidth * 0.85;
                                const maxScrollLeft = track.scrollWidth - track.clientWidth;
                                if (track.scrollLeft >= maxScrollLeft - 5) {
                                    track.scrollTo({ left: 0, behavior: 'smooth' });
                                } else {
                                    track.scrollBy({ left: amount, behavior: 'smooth' });
                                }
                            }, 3500);
                        },
                        resetTimer() {
                            if (this.interval) {
                                window.clearInterval(this.interval);
                                this.startTimer();
                            }
                        }
                    }"
                >
                    <div>
                        <p class="mb-3 home-kicker">{{ __('app.home.featured_kicker') }}</p>
                        <h2 class="home-heading">
                            {{ __('app.home.featured_title') }}
                        </h2>
                    </div>
                    @if ($carouselItems->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="home-secondary-button btn-outline"
                                aria-label="{{ __('app.actions.previous') }}"
                                @click="scroll(-1)"
                            >
                                ←
                            </button>
                            <button
                                type="button"
                                class="home-secondary-button btn-solid"
                                aria-label="{{ __('app.actions.next') }}"
                                @click="scroll(1)"
                            >
                                →
                            </button>
                        </div>
                    @endif
                </div>

                @if ($carouselItems->isNotEmpty())
                    <div
                        class="relative -mx-6 md:-mx-10"
                    >
                        <!-- Dark mode overlays -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 hidden w-24 bg-gradient-to-r from-[#0A0A0B] to-transparent md:dark:block"></div>
                        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 hidden w-24 bg-gradient-to-l from-[#0A0A0B] to-transparent md:dark:block"></div>

                        <!-- Light mode overlays -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 hidden w-20 bg-gradient-to-r from-[#F7F7F4] to-transparent md:block dark:hidden"></div>
                        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 hidden w-20 bg-gradient-to-l from-[#F7F7F4] to-transparent md:block dark:hidden"></div>
                        <div
                            id="featured-equipment-track"
                            class="flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-6 pb-4 md:px-10"
                            style="scrollbar-width: none;"
                        >
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
                            <article data-carousel-card class="group home-carousel-card">
                                <div class="flex w-full flex-col">
                                <div class="relative aspect-[4/3] overflow-hidden home-carousel-card-img-bg">
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemName }}"
                                        class="h-full w-full object-contain p-4 transition duration-500 group-hover:scale-[1.03]"
                                        onerror="this.onerror=null;this.src='{{ site_asset('MANAKE-FAV-M.png') }}';"
                                        loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $loop->index === 0 ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                    <div class="absolute left-3 top-3 rounded-sm border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] home-badge-category">
                                        {{ $itemCategory }}
                                    </div>
                                    <div class="absolute right-3 top-3 rounded-sm border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $itemStatusClass }}">
                                        {{ $itemStatusLabel }}
                                    </div>
                                    <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/60 to-transparent card-img-overlay"></div>
                                </div>
                                <div class="flex flex-1 flex-col gap-3 p-5">
                                    <div>
                                        <h3 class="text-base font-semibold leading-snug">{{ $itemName }}</h3>
                                        <p class="mt-1 text-xs opacity-60">{{ $itemCategory }}</p>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-3 py-2.5 home-carousel-card-meta">
                                        <div>
                                            <p class="text-[10px] uppercase tracking-[0.22em] opacity-60">{{ __('app.home.price_label') }}</p>
                                            <p class="mt-1 text-sm font-semibold">
                                                Rp {{ number_format($itemPrice, 0, ',', '.') }} <span class="opacity-60">/ {{ __('app.product.day_label') }}</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] uppercase tracking-[0.22em] opacity-60">{{ __('app.home.status_label') }}</p>
                                            <p class="mt-1 text-sm font-semibold {{ $isAvailable ? 'text-emerald-500 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-200' }}">
                                                {{ $isAvailable ? __('app.home.status_available') : __('app.home.status_booked') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-auto">
                                        <a href="{{ $itemUrl }}" class="home-primary-button {{ $isAvailable ? 'btn-available' : 'btn-booked' }}">
                                            {{ $isAvailable ? __('app.home.btn_view_book') : __('app.home.btn_view_details') }}
                                            @if($isAvailable)
                                                <span aria-hidden="true">→</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                                </div>
                            </article>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-[1.35rem] border p-8 text-center home-card">
                        <p class="text-base font-semibold">{{ __('app.home.empty_featured') }}</p>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 opacity-75">{{ __('app.home.empty_featured_desc') }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section id="about" class="border-y border-[#1A1A1E] bg-[#111113] py-24 md:py-28">
            <div class="mx-auto grid max-w-7xl gap-14 px-6 md:px-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="mb-3 home-kicker">{{ __('app.home.about_kicker') }}</p>
                    <h2 class="home-heading max-w-xl">
                        {{ __('app.home.about_title') }}
                    </h2>
                    <p class="mt-6 max-w-2xl home-copy">
                        {{ __('app.home.about_desc') }}
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['title' => __('app.home.pillar_1_title'), 'body' => __('app.home.pillar_1_desc')],
                        ['title' => __('app.home.pillar_2_title'), 'body' => __('app.home.pillar_2_desc')],
                        ['title' => __('app.home.pillar_3_title'), 'body' => __('app.home.pillar_3_desc')],
                        ['title' => __('app.home.pillar_4_title'), 'body' => __('app.home.pillar_4_desc')],
                    ] as $pillar)
                        <div class="home-card">
                            <h3 class="text-sm font-semibold">{{ $pillar['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 opacity-80">{{ $pillar['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="cara-sewa" class="bg-[#0A0A0B] py-24 md:py-28">
            <div class="mx-auto max-w-7xl px-6 md:px-10">
                <div class="max-w-2xl">
                    <p class="mb-3 home-kicker">{{ __('app.home.flow_kicker') }}</p>
                    <h2 class="home-heading">
                        {{ __('app.home.flow_title') }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['number' => __('app.home.step_1_num'), 'title' => __('app.home.step_1_title'), 'body' => __('app.home.step_1_desc')],
                        ['number' => __('app.home.step_2_num'), 'title' => __('app.home.step_2_title'), 'body' => __('app.home.step_2_desc')],
                        ['number' => __('app.home.step_3_num'), 'title' => __('app.home.step_3_title'), 'body' => __('app.home.step_3_desc')],
                        ['number' => __('app.home.step_4_num'), 'title' => __('app.home.step_4_title'), 'body' => __('app.home.step_4_desc')],
                    ] as $step)
                        <article class="home-card">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold tracking-[0.24em] home-card-number">{{ $step['number'] }}</span>
                                <span class="text-xs font-medium opacity-60">›</span>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold">{{ $step['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 opacity-80">{{ $step['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        @include('partials.footer')
    </div>
@endsection

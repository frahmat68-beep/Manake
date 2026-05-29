@extends('layouts.landing')

@php
    $statusValue = (string) ($equipment->status ?? ($equipment->stock > 0 ? 'ready' : 'unavailable'));
    $imagePath = $equipment->image_path ?? $equipment->image;
    $fallbackImage = site_asset('MANAKE-FAV-M.png');
    $mainImage = site_media_url($imagePath) ?: $fallbackImage;
    $gallery = $mainImage ? [$mainImage] : [];
    $reservedUnits = (int) ($equipment->reserved_units ?? 0);
    $availableUnits = (int) $equipment->available_units;
    $canRent = $statusValue === 'ready' && (int) $equipment->stock > 0;
    $statusLabel = match ($statusValue) {
        'maintenance' => __('app.product.status_maintenance'),
        'unavailable' => __('app.product.status_unavailable'),
        'ready' => $availableUnits > 0
            ? __('app.product.status_available')
            : __('app.product.status_fully_booked'),
        default => $availableUnits > 0
            ? __('app.product.status_available')
            : __('app.product.status_unavailable'),
    };
    $statusClass = match ($statusValue) {
        'maintenance' => 'border-amber-400/35 bg-amber-950/80 text-amber-200',
        'unavailable' => 'border-rose-400/35 bg-rose-950/80 text-rose-200',
        'ready' => $availableUnits > 0
            ? 'border-emerald-400/35 bg-emerald-950/80 text-emerald-200'
            : 'border-amber-400/35 bg-amber-950/80 text-amber-200',
        default => $availableUnits > 0
            ? 'border-emerald-400/35 bg-emerald-950/80 text-emerald-200'
            : 'border-rose-400/35 bg-rose-950/80 text-rose-200',
    };
    $bookingRanges = collect($bookingRanges ?? []);
    $specifications = $equipment->normalizedSpecifications();
    $availabilityEndpoint = route('product.availability', $equipment->slug);
    $bookingMinDate = now()->toDateString();
    $bookingMaxDate = now()->addMonthsNoOverflow(3)->toDateString();
    $lockDates = request()->boolean('lock_dates');
    $prefillStartDate = trim((string) (old('rental_start_date') ?: request('rental_start_date', request('start_date', ''))));
    $prefillEndDate = trim((string) (old('rental_end_date') ?: request('rental_end_date', request('end_date', ''))));
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillStartDate)) {
        $prefillStartDate = '';
    }
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillEndDate)) {
        $prefillEndDate = '';
    }
    if ($prefillStartDate !== '' && ($prefillStartDate < $bookingMinDate || $prefillStartDate > $bookingMaxDate)) {
        $prefillStartDate = '';
    }
    if ($prefillEndDate !== '' && ($prefillEndDate < $bookingMinDate || $prefillEndDate > $bookingMaxDate)) {
        $prefillEndDate = '';
    }
    if ($lockDates && ($prefillStartDate === '' || $prefillEndDate === '')) {
        $lockDates = false;
    }

    $resolveProductCategoryName = static function ($category): string {
        $rawName = trim((string) ($category->name ?? ''));
        $slug = strtolower(trim((string) ($category->slug ?? '')));
        if (! app()->isLocale('en')) {
            return $rawName;
        }

        return match ($slug) {
            'aksesoris', 'accessories', 'aksesori' => 'Accessories',
            'kamera', 'camera' => 'Camera',
            'lensa', 'lens' => 'Lens',
            'lampu', 'lighting' => 'Lighting',
            'audio' => 'Audio',
            'drone' => 'Drone',
            'stabilizer', 'stabilizer-gimbal', 'gimbal' => 'Stabilizer',
            'monitor-wireless-control', 'monitor-and-wireless-control', 'monitor-wireless' => 'Monitor & Wireless Control',
            default => $rawName,
        };
    };
@endphp

@section('title', $equipment->name)
@section('meta_description', __('app.product.meta'))

@push('head')
    <style>
        .no-spinner {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .product-detail-page {
            --product-accent: #D4A843;
            --product-accent-hover: #E0BA5D;
            --product-accent-text: #0A0A0B;
            --product-accent-soft: rgba(212, 168, 67, 0.12);
            --product-accent-border: rgba(212, 168, 67, 0.30);
            --product-bg: #0A0A0B;
            --product-surface: rgba(17, 17, 19, 0.70);
            --product-surface-soft: rgba(17, 17, 19, 0.48);
            --product-surface-strong: #111113;
            --product-inner: rgba(10, 10, 11, 0.48);
            --product-border: rgba(255, 255, 255, 0.08);
            --product-text: #E8E8EC;
            --product-muted: #A0A0A8;
        }

        html[data-theme-resolved="light"] .product-detail-page {
            --product-accent: #2563EB;
            --product-accent-hover: #1D4ED8;
            --product-accent-text: #FFFFFF;
            --product-accent-soft: rgba(37, 99, 235, 0.08);
            --product-accent-border: rgba(37, 99, 235, 0.24);
            --product-bg: #F8FAFC;
            --product-surface: rgba(255, 255, 255, 0.92);
            --product-surface-soft: rgba(255, 255, 255, 0.82);
            --product-surface-strong: #FFFFFF;
            --product-inner: #F8FAFC;
            --product-border: #E5E7EB;
            --product-text: #111827;
            --product-muted: #4B5563;
        }

        .product-page-bg {
            background-color: var(--product-bg) !important;
            color: var(--product-text) !important;
        }

        .product-card {
            background: var(--product-surface) !important;
            border-color: var(--product-border) !important;
            color: var(--product-text) !important;
        }

        .product-card-soft {
            background: var(--product-surface-soft) !important;
            border-color: var(--product-border) !important;
            color: var(--product-text) !important;
        }

        .product-inner {
            background: var(--product-inner) !important;
            border-color: var(--product-border) !important;
        }

        .product-title {
            color: var(--product-text) !important;
        }

        .product-muted {
            color: var(--product-muted) !important;
        }

        .product-accent-text {
            color: var(--product-accent) !important;
        }

        .product-accent-bg {
            background: var(--product-accent) !important;
            background-color: var(--product-accent) !important;
            color: var(--product-accent-text) !important;
            border-color: var(--product-accent) !important;
        }

        .product-accent-bg:hover {
            background: var(--product-accent-hover) !important;
            background-color: var(--product-accent-hover) !important;
        }

        .product-accent-soft {
            background: var(--product-accent-soft) !important;
            border-color: var(--product-accent-border) !important;
            color: var(--product-accent) !important;
        }

        .product-accent-dot {
            background-color: var(--product-accent) !important;
        }

        .product-accent-border-hover:hover {
            border-color: var(--product-accent-border) !important;
            color: var(--product-accent) !important;
        }

        .product-date-input {
            background: var(--product-surface-strong) !important;
            border: 1px solid var(--product-border) !important;
            color: var(--product-text) !important;
            border-radius: 0.875rem !important;
        }

        .product-date-input:focus {
            border-color: var(--product-accent) !important;
            box-shadow: 0 0 0 2px var(--product-accent-soft) !important;
            outline: none !important;
        }

        html[data-theme-resolved="light"] .product-date-input {
            color-scheme: light !important;
        }

        html[data-theme-resolved="dark"] .product-date-input {
            color-scheme: dark !important;
        }

        html[data-theme-resolved="light"] .product-detail-page .product-card,
        html[data-theme-resolved="light"] .product-detail-page .product-card-soft {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.26);
        }

        .product-accent-link:hover {
            color: var(--product-accent) !important;
        }

        .product-feedback-success {
            border-color: rgba(16, 185, 129, 0.35) !important;
            background: rgba(6, 78, 59, 0.38) !important;
            color: #A7F3D0 !important;
            border-width: 1px !important;
        }
        .product-feedback-warning {
            border-color: rgba(245, 158, 11, 0.38) !important;
            background: rgba(120, 53, 15, 0.38) !important;
            color: #FDE68A !important;
            border-width: 1px !important;
        }
        .product-feedback-error {
            border-color: rgba(244, 63, 94, 0.38) !important;
            background: rgba(136, 19, 55, 0.38) !important;
            color: #FDA4AF !important;
            border-width: 1px !important;
        }
        html[data-theme-resolved="light"] .product-feedback-success {
            background: #ECFDF5 !important;
            color: #047857 !important;
        }
        html[data-theme-resolved="light"] .product-feedback-warning {
            background: #FFFBEB !important;
            color: #B45309 !important;
        }
        html[data-theme-resolved="light"] .product-feedback-error {
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }
    </style>
@endpush

@section('content')

    <div class="product-detail-page product-page-bg min-h-screen pt-6 pb-24">
        <!-- Breadcrumbs Navigation -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 mb-6">
            <a href="{{ route('catalog') }}" class="product-muted product-accent-link inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                {{ __('app.actions.back_to_catalog') }}
            </a>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 grid grid-cols-1 gap-8 lg:grid-cols-[1.2fr,0.8fr]">
            <!-- Left Column: Product Info, Visuals & Specs -->
            <div class="space-y-6">
                <!-- Title & Badge Block -->
                <div class="product-card rounded-3xl border p-6 sm:p-8 shadow-xl">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-[10px] font-extrabold uppercase tracking-widest product-accent-text">{{ $resolveProductCategoryName($equipment->category) }}</span>
                        <span class="h-1 w-1 rounded-full bg-[#1A1A1E]"></span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-widest {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight product-title sm:text-4xl">
                        {{ $equipment->name }}
                    </h1>
                </div>

                <!-- Main Product Image -->
                <div class="product-card rounded-3xl border p-6 shadow-xl">
                    <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-2xl product-inner sm:aspect-[16/10]">
                        <img
                            src="{{ $mainImage }}"
                            alt="{{ $equipment->name }}"
                            class="max-h-full max-w-full object-contain drop-shadow-md transition-all duration-500 hover:scale-[1.02]"
                            onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                        >
                    </div>
                </div>

                @if (count($gallery) > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach (array_slice($gallery, 1) as $image)
                            <button class="product-card rounded-2xl border p-3 transition hover:-translate-y-0.5" type="button">
                                <img
                                    src="{{ $image }}"
                                    alt="Gallery {{ $equipment->name }}"
                                    class="h-16 w-full object-contain transition-transform duration-300 hover:scale-[1.03]"
                                    onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                                    loading="lazy"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Streamlined Specifications -->
                <div class="product-card rounded-3xl border p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center gap-3.5 mb-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl product-accent-bg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold tracking-tight product-title">{{ __('app.product.specs') }}</h3>
                    </div>

                    @if ($specifications->isEmpty())
                        <div class="rounded-2xl border product-inner p-6 text-center">
                            <p class="text-sm font-medium product-muted">{{ __('app.product.spec_unavailable') }}</p>
                        </div>
                    @else
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3.5">
                            @foreach ($specifications as $specification)
                                <li class="flex items-start gap-3 text-sm product-muted leading-relaxed">
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full product-accent-dot"></span>
                                    <span>{{ $specification }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Right Column: Pricing & Booking -->
            <div class="space-y-6 lg:sticky lg:top-24 self-start">
                <!-- Pricing Card -->
                <div class="product-card rounded-3xl border p-6 sm:p-8 shadow-xl">
                    <div class="space-y-6">
                        <div class="space-y-1">
                            <p class="text-[9px] font-extrabold uppercase tracking-widest product-accent-text">{{ __('app.product.price_per_day') }}</p>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-3xl font-extrabold tracking-tight product-title">Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}</span>
                                <span class="text-xs font-bold uppercase tracking-wider product-muted">{{ __('app.product.per_day') }}</span>
                            </div>
                        </div>

                        <!-- Stock Status -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <div class="product-inner rounded-2xl border p-3 text-center">
                                <p class="text-[9px] font-extrabold uppercase tracking-wider product-muted">{{ __('app.product.total_stock') }}</p>
                                <p class="mt-1 text-base font-extrabold product-title">{{ $equipment->stock }}</p>
                            </div>
                            <div class="product-inner rounded-2xl border p-3 text-center">
                                <p class="text-[9px] font-extrabold uppercase tracking-wider product-muted">{{ __('app.product.in_use') }}</p>
                                <p class="mt-1 text-base font-extrabold product-accent-text">{{ $reservedUnits }}</p>
                            </div>
                            <div class="product-inner rounded-2xl border p-3 text-center">
                                <p class="text-[9px] font-extrabold uppercase tracking-wider product-muted">{{ __('app.product.available_stock') }}</p>
                                <p class="mt-1 text-base font-extrabold {{ $availableUnits > 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $availableUnits }}</p>
                            </div>
                        </div>

                        <!-- Buffer / Blocked Schedules -->
                        <div class="product-inner rounded-2xl border p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="h-1.5 w-1.5 rounded-full product-accent-dot animate-pulse"></div>
                                <p class="text-[9px] font-extrabold uppercase tracking-widest product-accent-text">{{ __('app.product.schedule_title') }}</p>
                            </div>
                            
                            @if ($bookingRanges->isEmpty())
                                <p class="text-xs font-medium product-muted italic">{{ __('app.product.no_active_schedule') }}</p>
                            @else
                                <p class="mb-3 text-[10px] font-medium product-muted leading-normal">{{ __('app.product.blocked_schedule_note') }}</p>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach ($bookingRanges as $range)
                                        @php
                                            $rangeType = $range['type'] ?? 'booking';
                                            $isCurrentUserSchedule = (bool) ($range['is_current_user'] ?? false);
                                            $rangeLabel = match ($rangeType) {
                                                'buffer_before', 'buffer_after' => $isCurrentUserSchedule ? __('app.product.my_buffer_label') : __('app.product.buffer_label'),
                                                'maintenance' => __('app.product.maintenance_label'),
                                                'booking' => $isCurrentUserSchedule ? __('app.product.my_booking_label') : __('app.product.booked_label'),
                                                default => __('app.product.booked_label'),
                                            };
                                            $rangeDotClass = match ($rangeType) {
                                                'buffer_before', 'buffer_after' => 'bg-indigo-500',
                                                'maintenance' => 'bg-rose-500',
                                                default => 'bg-amber-500',
                                            };
                                            $startDate = \Carbon\Carbon::parse($range['start_date'])->translatedFormat('d M Y');
                                            $endDate = \Carbon\Carbon::parse($range['end_date'])->translatedFormat('d M Y');
                                            $dateText = $startDate === $endDate ? $startDate : ($startDate . ' - ' . $endDate);
                                        @endphp
                                        <div class="flex items-center gap-2 px-3 py-2 product-inner bg-[#0A0A0B]/40 rounded-xl border border-white/5 shadow-sm">
                                            <div class="h-1.5 w-1.5 shrink-0 rounded-full {{ $rangeDotClass }}"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold product-title truncate">{{ $dateText }}</p>
                                                <p class="text-[9px] font-extrabold product-muted uppercase tracking-tight truncate">
                                                    {{ $rangeLabel }} 
                                                    @if (($range['qty'] ?? 0) > 0) • {{ __('app.product.qty_label') }} {{ $range['qty'] }} @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Rental Form Card -->
                <div
                    id="rental-summary"
                    data-price="{{ $equipment->price_per_day }}"
                    data-availability-url="{{ $availabilityEndpoint }}"
                    data-min-date="{{ $bookingMinDate }}"
                    data-max-date="{{ $bookingMaxDate }}"
                    data-lock-dates="{{ $lockDates ? '1' : '0' }}"
                    data-locked-start="{{ $prefillStartDate }}"
                    data-locked-end="{{ $prefillEndDate }}"
                    class="product-card rounded-3xl border p-6 sm:p-8 shadow-xl"
                >
                    <h3 class="text-base font-bold tracking-tight product-title mb-5">{{ __('app.product.rental_date') }}</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider product-muted ml-0.5">{{ __('app.product.start_date') }}</label>
                                <input
                                    id="start-date"
                                    type="date"
                                    name="rental_start_date"
                                    form="rent-form"
                                    min="{{ $bookingMinDate }}"
                                    max="{{ $bookingMaxDate }}"
                                    value="{{ $prefillStartDate }}"
                                    required
                                    @readonly($lockDates)
                                    class="product-date-input min-h-14 w-full cursor-pointer px-4 text-base {{ $lockDates ? 'cursor-not-allowed opacity-60' : '' }}"
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider product-muted ml-0.5">{{ __('app.product.end_date') }}</label>
                                <input
                                    id="end-date"
                                    type="date"
                                    name="rental_end_date"
                                    form="rent-form"
                                    min="{{ $bookingMinDate }}"
                                    max="{{ $bookingMaxDate }}"
                                    value="{{ $prefillEndDate }}"
                                    required
                                    @readonly($lockDates)
                                    class="product-date-input min-h-14 w-full cursor-pointer px-4 text-base {{ $lockDates ? 'cursor-not-allowed opacity-60' : '' }}"
                                >
                            </div>
                        </div>

                        @if ($lockDates)
                            <div class="mb-6 p-3.5 product-accent-soft rounded-xl border text-[10px] font-extrabold flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                {{ __('app.product.locked_dates_message') }}
                            </div>
                        @endif

                        @unless($lockDates)
                            <div class="mb-6 grid grid-cols-3 gap-2">
                                <button type="button" data-date-preset="today" class="product-inner product-title product-accent-border-hover rounded-xl border px-3 py-3 text-xs font-extrabold transition">
                                    {{ __('app.product.preset_today') }}
                                </button>
                                <button type="button" data-date-preset="tomorrow" class="product-inner product-title product-accent-border-hover rounded-xl border px-3 py-3 text-xs font-extrabold transition">
                                    {{ __('app.product.preset_tomorrow') }}
                                </button>
                                <button type="button" data-date-preset="weekend" class="product-inner product-title product-accent-border-hover rounded-xl border px-3 py-3 text-xs font-extrabold transition">
                                    {{ __('app.product.preset_3_days') }}
                                </button>
                            </div>
                        @endunless

                        <div class="space-y-3.5 mb-6">
                            <div class="flex items-center justify-between rounded-xl px-4 py-3 text-xs border product-inner">
                                <span class="font-bold product-muted">{{ __('app.product.duration') }}</span>
                                <span id="total-days" class="font-extrabold product-title text-sm">-</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl product-accent-bg px-4 py-4 text-xs relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                                <span class="relative font-bold text-current opacity-80">{{ __('app.product.estimate') }}</span>
                                <span id="total-price" class="relative text-xl font-extrabold">Rp -</span>
                            </div>
                            <div id="availability-feedback" class="hidden rounded-xl border px-4 py-3 text-[11px] font-bold leading-relaxed whitespace-pre-line animate-fade-in"></div>
                        </div>

                        <div class="space-y-3.5">
                            @guest
                                @if ($canRent)
                                    <a
                                        id="login-rent-button"
                                        href="{{ route('login', ['reason' => 'cart']) }}"
                                        class="product-accent-bg w-full rounded-xl text-center py-3.5 text-sm font-bold transition block"
                                    >
                                        {{ __('ui.actions.login_to_add') }}
                                    </a>
                                @else
                                    <button type="button" disabled class="product-inner product-muted w-full py-3.5 rounded-xl font-bold border cursor-not-allowed text-sm">
                                        {{ __('app.product.out_of_stock') }}
                                    </button>
                                @endif
                                <p class="text-[9px] font-extrabold product-muted text-center uppercase tracking-wider">{{ __('app.messages.login_to_cart') }}</p>
                            @endguest

                            @auth
                                <form id="rent-form" method="POST" action="{{ route('cart.add') }}" class="space-y-5" x-data="{ qty: 1, maxQty: {{ max((int) $equipment->stock, 1) }} }">
                                    @csrf
                                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
                                    <input type="hidden" name="name" value="{{ $equipment->name }}">
                                    <input type="hidden" name="slug" value="{{ $equipment->slug }}">
                                    <input type="hidden" name="category" value="{{ $equipment->category?->name }}">
                                    <input type="hidden" name="image" value="{{ $mainImage }}">
                                    <input type="hidden" name="price" value="{{ $equipment->price_per_day }}">
                                    
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-extrabold uppercase tracking-wider product-muted ml-0.5">{{ __('app.product.quantity') }}</label>
                                        <div class="relative flex items-center">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="absolute left-1.5 h-9 w-9 flex items-center justify-center rounded-lg border product-inner product-title product-accent-border-hover transition-all active:scale-95 text-sm font-bold">-</button>
                                            <input
                                                id="rent-qty"
                                                type="number"
                                                name="qty"
                                                min="1"
                                                :max="maxQty"
                                                x-model="qty"
                                                class="product-date-input no-spinner text-center font-extrabold text-sm h-12 w-full font-sans"
                                                required
                                            >
                                            <button type="button" @click="qty = Math.min(maxQty, qty + 1)" class="absolute right-1.5 h-9 w-9 flex items-center justify-center rounded-lg border product-inner product-title product-accent-border-hover transition-all active:scale-95 text-sm font-bold">+</button>
                                        </div>
                                    </div>

                                    <button
                                        id="add-to-cart-button"
                                        type="submit"
                                        class="product-accent-bg w-full rounded-xl disabled:opacity-50 disabled:translate-y-0 py-3.5 text-sm font-extrabold transition"
                                        @disabled(! $canRent)
                                    >
                                        {{ $canRent ? __('ui.actions.add_to_cart') : __('app.product.out_of_stock') }}
                                    </button>
                                </form>
                                <p class="text-[9px] font-extrabold product-muted text-center uppercase tracking-wider">{{ __('app.product.note') }}</p>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const summary = document.getElementById('rental-summary');
            if (!summary) return;

            const price = Number(summary.dataset.price || 0);
            const availabilityUrl = summary.dataset.availabilityUrl || '';
            const minDate = summary.dataset.minDate || '';
            const maxDate = summary.dataset.maxDate || '';
            const isLockedDates = summary.dataset.lockDates === '1';
            const lockedStartDate = summary.dataset.lockedStart || '';
            const lockedEndDate = summary.dataset.lockedEnd || '';
            const startInput = document.getElementById('start-date');
            const endInput = document.getElementById('end-date');
            const qtyInput = document.getElementById('rent-qty');
            const totalDays = document.getElementById('total-days');
            const totalPrice = document.getElementById('total-price');
            const availabilityFeedback = document.getElementById('availability-feedback');
            const addToCartButton = document.getElementById('add-to-cart-button');
            const loginRentButton = document.getElementById('login-rent-button');
            const rentForm = document.getElementById('rent-form');
            const locale = @json(app()->getLocale());
            let availabilityState = 'unknown';
            let debounceTimer = null;
            let availabilityRequestToken = 0;
            const addToCartDefaultText = addToCartButton ? addToCartButton.textContent : '';

            const formatIDR = (value) => `Rp ${value.toLocaleString('id-ID')}`;
            const formatDate = (dateValue) => {
                const parsed = new Date(`${dateValue}T00:00:00`);
                if (Number.isNaN(parsed.getTime())) {
                    return dateValue;
                }

                return new Intl.DateTimeFormat(locale === 'en' ? 'en-US' : 'id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }).format(parsed);
            };
            const isDateInAllowedWindow = (value) => {
                if (!value) return false;
                if (minDate && value < minDate) return false;
                if (maxDate && value > maxDate) return false;
                return true;
            };
            const toDateValue = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };
            const clampDateValue = (value) => {
                if (minDate && value < minDate) return minDate;
                if (maxDate && value > maxDate) return maxDate;

                return value;
            };
            const updateLoginRentHref = () => {
                if (!loginRentButton) return;
                const currentUrl = new URL(window.location.href);
                if (startInput?.value) currentUrl.searchParams.set('rental_start_date', startInput.value);
                if (endInput?.value) currentUrl.searchParams.set('rental_end_date', endInput.value);

                const loginUrl = new URL(@json(route('login', ['reason' => 'cart'])), window.location.origin);
                loginUrl.searchParams.set('redirect', currentUrl.toString());
                loginRentButton.href = loginUrl.toString();
            };

            const setAddToCartState = (enabled, label = null) => {
                if (!addToCartButton) return;
                addToCartButton.disabled = !enabled;
                if (label !== null) {
                    addToCartButton.textContent = label;
                } else if (addToCartDefaultText) {
                    addToCartButton.textContent = addToCartDefaultText;
                }
            };

            const setAvailabilityMessage = (message, tone = 'info') => {
                if (!availabilityFeedback) return;
                const classes = {
                    info: 'product-inner product-muted',
                    success: 'product-feedback-success',
                    warning: 'product-feedback-warning',
                    error: 'product-feedback-error',
                };
                availabilityFeedback.className = `rounded-xl border px-4 py-3 text-[11px] font-bold leading-relaxed whitespace-pre-line animate-fade-in ${classes[tone] || classes.info}`;
                availabilityFeedback.textContent = message;
                availabilityFeedback.classList.remove('hidden');
            };

            const clearAvailabilityMessage = () => {
                if (!availabilityFeedback) return;
                availabilityFeedback.classList.add('hidden');
                availabilityFeedback.textContent = '';
            };

            const updateTotal = () => {
                const qty = Math.max(Number.parseInt(qtyInput?.value || '1', 10) || 1, 1);

                if (!startInput.value || !endInput.value) {
                    totalDays.textContent = '-';
                    totalPrice.textContent = 'Rp -';
                    availabilityState = 'unknown';
                    clearAvailabilityMessage();
                    setAddToCartState(false);
                    updateLoginRentHref();
                    return;
                }

                if (!isDateInAllowedWindow(startInput.value) || !isDateInAllowedWindow(endInput.value)) {
                    totalDays.textContent = '-';
                    totalPrice.textContent = 'Rp -';
                    availabilityState = 'invalid';
                    setAddToCartState(false);
                    setAvailabilityMessage(@json(__('app.product.date_window_error')), 'error');
                    updateLoginRentHref();
                    return;
                }

                const startDate = new Date(startInput.value);
                const endDate = new Date(endInput.value);
                const diff = Math.ceil((endDate - startDate) / 86400000) + 1;

                if (Number.isNaN(diff) || diff <= 0) {
                    totalDays.textContent = '-';
                    totalPrice.textContent = 'Rp -';
                    availabilityState = 'invalid';
                    setAddToCartState(false);
                    setAvailabilityMessage(@json(__('app.product.invalid_range')), 'error');
                    updateLoginRentHref();
                    return;
                }

                totalDays.textContent = `${diff} {{ __('app.product.day_label') }}`;
                totalPrice.textContent = formatIDR(price * diff * qty);
                setAddToCartState(false, @json(__('app.product.checking_availability')));
                updateLoginRentHref();
            };

            const checkAvailability = async () => {
                if (!availabilityUrl || !startInput.value || !endInput.value) return availabilityState;
                const requestToken = ++availabilityRequestToken;
                const qty = Number(qtyInput?.value || '1');
                setAddToCartState(false, @json(__('app.product.checking_availability')));
                setAvailabilityMessage(@json(__('app.product.checking_availability')), 'info');

                try {
                    const params = new URLSearchParams({
                        start_date: startInput.value,
                        end_date: endInput.value,
                        qty: String(Number.isNaN(qty) ? 1 : Math.max(qty, 1)),
                    });
                    const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json();

                    if (!response.ok) throw new Error(payload.message || @json(__('ui.availability.not_available')));
                    if (requestToken !== availabilityRequestToken) {
                        return availabilityState;
                    }

                    availabilityState = payload.status || 'unknown';

                    if (availabilityState === 'available') {
                        setAddToCartState(true);
                        setAvailabilityMessage(payload.message || @json(__('ui.availability.available')), 'success');
                        return availabilityState;
                    }

                    const conflicts = Array.isArray(payload.conflicts) ? payload.conflicts.map(formatDate) : [];
                    const suggestions = Array.isArray(payload.suggestions)
                        ? payload.suggestions.map((item) => `${formatDate(item.start_date)} - ${formatDate(item.end_date)}`)
                        : [];

                    const lines = [payload.message || @json(__('ui.availability.not_available'))];
                    if (conflicts.length > 0) lines.push(`{{ __('app.product.availability_conflict_label') }} ${conflicts.slice(0, 4).join(', ')}`);
                    if (suggestions.length > 0) lines.push(`{{ __('app.product.availability_suggestions_label') }} ${suggestions.join(' | ')}`);
                    const detail = lines.join('\n');

                    setAddToCartState(false);
                    setAvailabilityMessage(detail, availabilityState === 'partially_available' ? 'warning' : 'error');
                    return availabilityState;
                } catch (error) {
                    availabilityState = 'error';
                    setAddToCartState(true);
                    const fallbackCheckMessage = @json(__('app.product.availability_recheck_note'));
                    setAvailabilityMessage(
                        `${error.message || @json(__('ui.availability.not_available'))}\n${fallbackCheckMessage}`,
                        'warning'
                    );
                    return availabilityState;
                }
            };

            const scheduleAvailabilityCheck = () => {
                if (!startInput.value || !endInput.value) return;
                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(checkAvailability, 260);
            };
            const applyPreset = (preset) => {
                if (!startInput || !endInput || isLockedDates) return;
                const today = new Date(`${minDate || toDateValue(new Date())}T00:00:00`);
                let start = new Date(today);
                let end = new Date(today);

                if (preset === 'tomorrow') {
                    start.setDate(start.getDate() + 1);
                    end = new Date(start);
                } else if (preset === 'weekend') {
                    end.setDate(end.getDate() + 2);
                }

                startInput.value = clampDateValue(toDateValue(start));
                endInput.value = clampDateValue(toDateValue(end));
                if (endInput.value < startInput.value) {
                    endInput.value = startInput.value;
                }
                applyDateInputLimits();
                updateTotal();
                scheduleAvailabilityCheck();
            };

            const applyDateInputLimits = () => {
                if (!startInput || !endInput) return;
                if (minDate) { startInput.min = minDate; endInput.min = minDate; }
                if (maxDate) { startInput.max = maxDate; endInput.max = maxDate; }
                if (startInput.value) endInput.min = startInput.value > minDate ? startInput.value : minDate;
            };

            const enforceLockedDates = () => {
                if (!isLockedDates || !startInput || !endInput) return;
                if (lockedStartDate) startInput.value = lockedStartDate;
                if (lockedEndDate) endInput.value = lockedEndDate;

                const keepLockedValue = (input, expected) => {
                    if (!input) return;
                    input.addEventListener('input', () => { if (expected) input.value = expected; });
                    input.addEventListener('change', () => { if (expected) input.value = expected; });
                    input.addEventListener('keydown', (e) => e.preventDefault());
                    input.setAttribute('aria-readonly', 'true');
                };

                keepLockedValue(startInput, lockedStartDate);
                keepLockedValue(endInput, lockedEndDate);
            };

            if (startInput && endInput) {
                [startInput, endInput].forEach((input) => {
                    input.addEventListener('click', () => {
                        if (isLockedDates || typeof input.showPicker !== 'function') return;
                        try {
                            input.showPicker();
                        } catch (error) {
                            input.focus();
                        }
                    });
                });

                startInput.addEventListener('change', () => {
                    if (startInput.value) endInput.min = startInput.value > minDate ? startInput.value : minDate;
                    else endInput.min = minDate;
                    updateTotal();
                    scheduleAvailabilityCheck();
                });
                startInput.addEventListener('input', () => {
                    if (startInput.value) endInput.min = startInput.value > minDate ? startInput.value : minDate;
                    else endInput.min = minDate;
                    updateTotal();
                    scheduleAvailabilityCheck();
                });
                endInput.addEventListener('change', () => { updateTotal(); scheduleAvailabilityCheck(); });
                endInput.addEventListener('input', () => { updateTotal(); scheduleAvailabilityCheck(); });
            }

            document.querySelectorAll('[data-date-preset]').forEach((button) => {
                button.addEventListener('click', () => applyPreset(button.dataset.datePreset || 'today'));
            });

            document.querySelectorAll('[data-scroll-to-rental]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    window.setTimeout(() => {
                        if (startInput && !isLockedDates) {
                            startInput.focus({ preventScroll: true });
                            if (typeof startInput.showPicker === 'function') {
                                try {
                                    startInput.showPicker();
                                } catch (error) {
                                    startInput.focus({ preventScroll: true });
                                }
                            }
                        }
                    }, 450);
                });
            });

            if (qtyInput) {
                qtyInput.addEventListener('change', scheduleAvailabilityCheck);
                qtyInput.addEventListener('input', scheduleAvailabilityCheck);
            }

            if (startInput && endInput) {
                applyDateInputLimits();
                enforceLockedDates();
                applyDateInputLimits();
                updateTotal();
                if (startInput.value && endInput.value) scheduleAvailabilityCheck();
            }

            if (rentForm && startInput && endInput) {
                rentForm.addEventListener('submit', async (event) => {
                    if (!startInput.value || !endInput.value) {
                        event.preventDefault();
                        alert(@json(__('app.product.select_dates_first')));
                        return;
                    }
                    const startDate = new Date(startInput.value);
                    const endDate = new Date(endInput.value);
                    if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()) || endDate < startDate) {
                        event.preventDefault();
                        alert(@json(__('app.product.invalid_range')));
                        return;
                    }
                    if (!isDateInAllowedWindow(startInput.value) || !isDateInAllowedWindow(endInput.value)) {
                        event.preventDefault();
                        alert(@json(__('app.product.date_window_error')));
                        return;
                    }
                    if (availabilityState !== 'available' && availabilityState !== 'error') {
                        event.preventDefault();
                        const checkedState = await checkAvailability();
                        if (checkedState === 'available' || checkedState === 'error') {
                            rentForm.submit();
                            return;
                        }
                        alert(@json(__('ui.availability.not_available')));
                    }
                });
            }
        })();
    </script>
@endsection

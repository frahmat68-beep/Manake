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
        'maintenance' => 'Maintenance',
        'unavailable' => 'Tidak Tersedia',
        'ready' => $availableUnits > 0 ? 'Tersedia' : 'Penuh / Sedang Disewa',
        default => $availableUnits > 0 ? 'Tersedia' : 'Tidak Tersedia',
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
    </style>
@endpush

@section('content')

    <div class="manake-page">
        <section class="manake-section">
            <div class="manake-page-frame">
                <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 shadow-2xl">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2.5 mb-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-widest {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-extrabold leading-tight tracking-tight text-[#E8E8EC] sm:text-3xl">
                                {{ $equipment->name }}
                            </h1>
                            <div class="mt-5 flex flex-wrap gap-3">
                                @if ($canRent)
                                    <a href="#rental-summary" data-scroll-to-rental class="mk-button-primary px-5 py-2.5 text-sm">
                                        Pilih Tanggal Sewa
                                    </a>
                                @endif
                                <a href="{{ route('catalog') }}" class="mk-button-secondary px-5 py-2.5 text-sm">
                                    {{ __('app.actions.back_to_catalog') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="manake-section pb-24">
            <div class="manake-page-frame grid grid-cols-1 gap-6 lg:grid-cols-[1.1fr,0.9fr] lg:gap-8">
                <!-- Left Column: Visual & Specs -->
                <div class="order-2 space-y-6 lg:order-1">
                    <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6">
                        <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-md bg-[#0A0A0B] sm:aspect-[16/10]">
                            <img
                                src="{{ $mainImage }}"
                                alt="{{ $equipment->name }}"
                                class="max-h-full max-w-full object-contain drop-shadow-md transition-transform duration-500 hover:scale-[1.01]"
                                onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                            >
                        </div>
                    </div>

                    @if (count($gallery) > 1)
                        <div class="grid grid-cols-4 gap-3">
                            @foreach (array_slice($gallery, 1) as $image)
                                <button class="mk-card p-3 hover:-translate-y-0.5 group" type="button">
                                    <img
                                        src="{{ $image }}"
                                        alt="Gallery {{ $equipment->name }}"
                                        class="h-16 w-full object-contain transition-transform duration-300 group-hover:scale-[1.03]"
                                        onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                                        loading="lazy"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 sm:p-8">
                        <div class="flex items-center gap-3.5 mb-6">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#D4A843] text-[#0A0A0B]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h3 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('app.product.specs') }}</h3>
                        </div>

                        @if ($specifications->isEmpty())
                            <div class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-6 text-center">
                                <p class="text-sm font-medium text-[#A0A0A8]">{{ __('app.product.spec_unavailable') }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($specifications as $specification)
                                    <div class="flex items-start gap-3 rounded-md border border-transparent p-3.5 transition-colors duration-300 hover:border-[#1A1A1E] hover:bg-[#0A0A0B]">
                                        <div class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#D4A843]"></div>
                                        <span class="text-sm font-semibold leading-relaxed text-[#E8E8EC]">{{ $specification }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Pricing & Booking -->
                <div class="order-1 space-y-6 lg:order-2 lg:sticky lg:top-24">
                    <!-- Pricing Card -->
                    <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 sm:p-8">
                        <div class="flex flex-col gap-5">
                            <div class="space-y-1">
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-[#D4A843]/80">{{ __('app.product.price_per_day') }}</p>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-3xl font-extrabold tracking-tight text-[#E8E8EC]">Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#A0A0A8]">{{ __('app.product.per_day') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-3 text-center">
                                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-[#A0A0A8]">{{ __('app.product.total_stock') }}</p>
                                    <p class="mt-1.5 text-lg font-extrabold text-[#E8E8EC]">{{ $equipment->stock }}</p>
                                </div>
                                <div class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-3 text-center">
                                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-[#A0A0A8]">{{ __('app.product.in_use') }}</p>
                                    <p class="mt-1.5 text-lg font-extrabold text-[#D4A843]">{{ $reservedUnits }}</p>
                                </div>
                                <div class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-3 text-center">
                                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-[#A0A0A8]">{{ __('app.product.available_stock') }}</p>
                                    <p class="mt-1.5 text-lg font-extrabold {{ $availableUnits > 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $availableUnits }}</p>
                                </div>
                            </div>

                            <div class="mk-card-soft p-5">
                                <div class="flex items-center gap-2 mb-3.5">
                                    <div class="h-1.5 w-1.5 rounded-full bg-[#D4A843] animate-pulse"></div>
                                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-[#D4A843]">{{ __('app.product.schedule_title') }}</p>
                                </div>
                                
                                @if ($bookingRanges->isEmpty())
                                    <p class="text-xs font-semibold text-[#A0A0A8] italic">{{ __('app.product.no_active_schedule') }}</p>
                                @else
                                    <p class="mb-3 text-[11px] font-medium text-[#A0A0A8] leading-relaxed">{{ __('app.product.blocked_schedule_note') }}</p>
                                    <div class="space-y-2">
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
                                            <div class="flex items-center gap-2.5 p-2.5 bg-[#111113] rounded-xl border border-[#1A1A1E] shadow-sm">
                                                <div class="h-1.5 w-1.5 shrink-0 rounded-full {{ $rangeDotClass }}"></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-[#E8E8EC] truncate">{{ $dateText }}</p>
                                                    <p class="text-[9px] font-extrabold text-[#A0A0A8] uppercase tracking-tight truncate mt-0.5">
                                                        {{ $rangeLabel }} 
                                                        @if (($range['qty'] ?? 0) > 0) • Qty {{ $range['qty'] }} @endif
                                                        @if (($range['reason'] ?? null)) • {{ $range['reason'] }} @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <p class="mt-3.5 text-[9px] font-semibold text-[#A0A0A8] text-center italic">{{ __('app.product.checkout_reject_note') }}</p>
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
                        class="mk-card p-6 sm:p-8"
                    >
                        <h3 class="text-lg font-bold tracking-tight text-[#E8E8EC] mb-6">{{ __('app.product.rental_date') }}</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider text-[#A0A0A8] ml-0.5">{{ __('app.product.start_date') }}</label>
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
                                    class="mk-input min-h-14 cursor-pointer text-base {{ $lockDates ? 'cursor-not-allowed opacity-60' : '' }}"
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider text-[#A0A0A8] ml-0.5">{{ __('app.product.end_date') }}</label>
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
                                    class="mk-input min-h-14 cursor-pointer text-base {{ $lockDates ? 'cursor-not-allowed opacity-60' : '' }}"
                                >
                            </div>
                        </div>

                        @if ($lockDates)
                            <div class="mb-6 p-3.5 bg-[#111113] rounded-xl border border-[#1A1A1E] text-[10px] font-extrabold text-[#D4A843] flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                {{ __('Tanggal sewa dikunci mengikuti pesanan di cart. Untuk ubah tanggal, edit dulu item di cart.') }}
                            </div>
                        @endif

                        @unless($lockDates)
                            <div class="mb-6 grid grid-cols-3 gap-2">
                                <button type="button" data-date-preset="today" class="rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-3 text-xs font-extrabold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                    Hari ini
                                </button>
                                <button type="button" data-date-preset="tomorrow" class="rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-3 text-xs font-extrabold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                    Besok
                                </button>
                                <button type="button" data-date-preset="weekend" class="rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-3 text-xs font-extrabold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                    3 hari
                                </button>
                            </div>
                        @endunless

                        <div class="space-y-3.5 mb-6">
                            <div class="flex items-center justify-between rounded-xl bg-[#111113]/50 px-4 py-3 text-xs border border-[#1A1A1E]">
                                <span class="font-bold text-[#A0A0A8]">{{ __('app.product.duration') }}</span>
                                <span id="total-days" class="font-extrabold text-[#E8E8EC] text-sm">-</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-[#D4A843] px-4 py-4 text-xs text-[#0A0A0B] relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                                <span class="relative font-bold text-[#0A0A0B]/80">{{ __('app.product.estimate') }}</span>
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
                                        class="mk-button-primary w-full text-center py-3.5 text-sm"
                                    >
                                        {{ __('ui.actions.login_to_add') }}
                                    </a>
                                @else
                                    <button type="button" disabled class="w-full py-3.5 rounded-xl bg-[#111113] text-[#66666C] font-bold border border-[#1A1A1E] cursor-not-allowed text-sm">
                                        {{ __('app.product.out_of_stock') }}
                                    </button>
                                @endif
                                <p class="text-[9px] font-extrabold text-[#A0A0A8] text-center uppercase tracking-wider">{{ __('app.messages.login_to_cart') }}</p>
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
                                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-[#A0A0A8] ml-0.5">Kuantitas</label>
                                        <div class="relative flex items-center">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="absolute left-1.5 h-9 w-9 flex items-center justify-center rounded-lg border border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] hover:border-[#D4A843]/40 transition-all active:scale-95 text-sm font-bold">-</button>
                                            <input
                                                id="rent-qty"
                                                type="number"
                                                name="qty"
                                                min="1"
                                                :max="maxQty"
                                                x-model="qty"
                                                class="mk-input no-spinner text-center font-extrabold text-sm h-12"
                                                required
                                            >
                                            <button type="button" @click="qty = Math.min(maxQty, qty + 1)" class="absolute right-1.5 h-9 w-9 flex items-center justify-center rounded-lg border border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] hover:border-[#D4A843]/40 transition-all active:scale-95 text-sm font-bold">+</button>
                                        </div>
                                    </div>

                                    <button
                                        id="add-to-cart-button"
                                        type="submit"
                                        class="mk-button-primary w-full disabled:opacity-50 disabled:translate-y-0 py-3.5 text-sm font-extrabold"
                                        @disabled(! $canRent)
                                    >
                                        {{ $canRent ? __('ui.actions.add_to_cart') : __('app.product.out_of_stock') }}
                                    </button>
                                </form>
                                <p class="text-[9px] font-extrabold text-[#A0A0A8] text-center uppercase tracking-wider">{{ __('app.product.note') }}</p>
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
                    info: 'border-[#1A1A1E] bg-[#111113] text-[#A0A0A8]',
                    success: 'border-emerald-400/35 bg-emerald-950/70 text-emerald-200',
                    warning: 'border-amber-400/35 bg-amber-950/70 text-amber-200',
                    error: 'border-rose-400/35 bg-rose-950/70 text-rose-200',
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
                    setAvailabilityMessage(@json(__('Tanggal sewa hanya bisa dipilih dari hari ini sampai 3 bulan ke depan.')), 'error');
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
                totalPrice.textContent = formatIDR(price * diff);
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
                    const fallbackCheckMessage = @json(__('Ketersediaan akan dicek ulang saat masuk keranjang.'));
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
                        alert(@json(__('Tanggal sewa hanya bisa dipilih dari hari ini sampai 3 bulan ke depan.')));
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

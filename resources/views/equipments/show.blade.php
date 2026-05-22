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
        'maintenance' => 'border-amber-500/20 bg-amber-950/75 text-amber-300',
        'unavailable' => 'border-rose-500/20 bg-rose-950/75 text-rose-300',
        'ready' => $availableUnits > 0
            ? 'border-emerald-500/20 bg-emerald-950/75 text-emerald-300'
            : 'border-amber-500/20 bg-amber-950/75 text-amber-200',
        default => $availableUnits > 0
            ? 'border-emerald-500/20 bg-emerald-950/75 text-emerald-300'
            : 'border-rose-500/20 bg-rose-950/75 text-rose-300',
    };
    $bookingRanges = collect($bookingRanges ?? []);
    $specificationSource = trim((string) ($equipment->specifications ?? $equipment->description ?? ''));
    $specifications = collect(preg_split('/\\r\\n|\\r|\\n/', $specificationSource ?: ''))
        ->map(fn ($line) => trim((string) preg_replace('/^[-*\\x{2022}\\s]+/u', '', $line)))
        ->filter()
        ->values();
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
                            <div class="flex items-center gap-2.5 mb-3">
                                <span class="rounded-full border border-[#1A1A1E] bg-[#111113]/95 px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-wider text-[#D4A843] backdrop-blur-md">
                                    {{ $equipment->category?->name ?? __('app.category.title') }}
                                </span>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-extrabold leading-tight tracking-tight text-[#E8E8EC] sm:text-3xl">
                                {{ $equipment->name }}
                            </h1>
                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[#A0A0A8] sm:text-base">
                                {{ __('app.product.meta') }}
                            </p>
                        </div>
                        <a href="{{ route('catalog') }}" class="inline-flex shrink-0 self-start rounded-md border border-[#1A1A1E] bg-[#111113] px-5 py-2.5 text-xs font-bold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843] sm:self-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            {{ __('app.actions.back_to_catalog') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="manake-section pb-24">
            <div class="manake-page-frame grid grid-cols-1 gap-6 lg:grid-cols-[1.1fr,0.9fr] lg:gap-8">
                <!-- Left Column: Visual & Specs -->
                <div class="space-y-6">
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
                <div class="space-y-6">
                    <!-- Pricing Card -->
                    <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-6 sm:p-8">
                        <div class="flex flex-col gap-5">
                            <div class="space-y-1">
                                <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-[#D4A843]/80">{{ __('app.product.price_per_day') }}</p>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-3xl font-extrabold tracking-tight text-[#E8E8EC]">Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#A0A0A8]">/ {{ __('app.product.per_day') }}</span>
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
                                    <div class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ __('app.product.schedule_title') }}</p>
                                </div>
                                
                                @if ($bookingRanges->isEmpty())
                                    <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 italic">{{ __('app.product.no_active_schedule') }}</p>
                                @else
                                    <p class="mb-3 text-[11px] font-medium text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('app.product.blocked_schedule_note') }}</p>
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
                                            <div class="flex items-center gap-2.5 p-2.5 bg-white/70 dark:bg-slate-900/60 rounded-xl border border-white/60 dark:border-slate-800/40 shadow-sm">
                                                <div class="h-1.5 w-1.5 shrink-0 rounded-full {{ $rangeDotClass }}"></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $dateText }}</p>
                                                    <p class="text-[9px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-tight truncate mt-0.5">
                                                        {{ $rangeLabel }} 
                                                        @if (($range['qty'] ?? 0) > 0) • Qty {{ $range['qty'] }} @endif
                                                        @if (($range['reason'] ?? null)) • {{ $range['reason'] }} @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <p class="mt-3.5 text-[9px] font-semibold text-slate-400 dark:text-slate-500 text-center italic">{{ __('app.product.checkout_reject_note') }}</p>
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
                        <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100 mb-6">{{ __('app.product.rental_date') }}</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 ml-0.5">{{ __('app.product.start_date') }}</label>
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
                                    class="mk-input {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-400 opacity-60' : '' }}"
                                >
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 ml-0.5">{{ __('app.product.end_date') }}</label>
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
                                    class="mk-input {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-400 opacity-60' : '' }}"
                                >
                            </div>
                        </div>

                        @if ($lockDates)
                            <div class="mb-6 p-3.5 bg-blue-50/80 dark:bg-blue-950/20 rounded-xl border border-blue-100/50 dark:border-blue-900/30 text-[10px] font-extrabold text-blue-700 dark:text-blue-400 flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                {{ __('Tanggal sewa dikunci mengikuti pesanan di cart. Untuk ubah tanggal, edit dulu item di cart.') }}
                            </div>
                        @endif

                        <div class="space-y-3.5 mb-6">
                            <div class="flex items-center justify-between rounded-xl bg-white/50 dark:bg-slate-900/30 px-4.5 py-3 text-xs border border-white/60 dark:border-slate-800/40">
                                <span class="font-bold text-slate-400 dark:text-slate-500">{{ __('app.product.duration') }}</span>
                                <span id="total-days" class="font-extrabold text-slate-950 dark:text-slate-50 text-sm">-</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-blue-600 px-4.5 py-4 text-xs text-white relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                                <span class="relative font-bold text-white/80">{{ __('app.product.estimate') }}</span>
                                <span id="total-price" class="relative text-xl font-extrabold">Rp -</span>
                            </div>
                            <div id="availability-feedback" class="hidden rounded-xl border px-4.5 py-3 text-[11px] font-bold leading-relaxed whitespace-pre-line animate-fade-in"></div>
                        </div>

                        <div class="space-y-3.5">
                            @guest
                                @if ($canRent)
                                    <a
                                        href="{{ route('login', ['reason' => 'cart']) }}"
                                        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                        class="mk-button-primary w-full text-center py-3.5 text-sm"
                                    >
                                        {{ __('ui.actions.login_to_add') }}
                                    </a>
                                @else
                                    <button type="button" disabled class="w-full py-3.5 rounded-xl bg-slate-100 text-slate-400 font-bold border border-slate-200 cursor-not-allowed text-sm">
                                        {{ __('app.product.out_of_stock') }}
                                    </button>
                                @endif
                                <p class="text-[9px] font-extrabold text-slate-400 dark:text-slate-500 text-center uppercase tracking-wider">{{ __('app.messages.login_to_cart') }}</p>
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
                                        <label class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 ml-0.5">Kuantitas</label>
                                        <div class="relative flex items-center">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="absolute left-1.5 h-9 w-9 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-350 hover:bg-slate-50 transition-all active:scale-95 text-sm font-bold">-</button>
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
                                            <button type="button" @click="qty = Math.min(maxQty, qty + 1)" class="absolute right-1.5 h-9 w-9 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-350 hover:bg-slate-50 transition-all active:scale-95 text-sm font-bold">+</button>
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
                                <p class="text-[9px] font-extrabold text-slate-400 dark:text-slate-500 text-center uppercase tracking-wider">{{ __('app.product.note') }}</p>
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
            const rentForm = document.getElementById('rent-form');
            const locale = @json(app()->getLocale());
            let availabilityState = 'unknown';
            let debounceTimer = null;

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

            const setAddToCartState = (enabled) => {
                if (!addToCartButton) return;
                addToCartButton.disabled = !enabled;
            };

            const setAvailabilityMessage = (message, tone = 'info') => {
                if (!availabilityFeedback) return;
                const classes = {
                    info: 'border-blue-100 bg-blue-50/80 text-blue-750 dark:border-blue-900/30 dark:bg-blue-950/20 dark:text-blue-400',
                    success: 'border-emerald-100 bg-emerald-50/80 text-emerald-750 dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-400',
                    warning: 'border-amber-100 bg-amber-50/80 text-amber-750 dark:border-amber-900/30 dark:bg-amber-950/20 dark:text-amber-400',
                    error: 'border-rose-100 bg-rose-50/80 text-rose-750 dark:border-rose-900/30 dark:bg-rose-950/20 dark:text-rose-400',
                };
                availabilityFeedback.className = `rounded-xl border px-4.5 py-3 text-[11px] font-bold leading-relaxed whitespace-pre-line animate-fade-in ${classes[tone] || classes.info}`;
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
                    setAddToCartState(true);
                    return;
                }

                if (!isDateInAllowedWindow(startInput.value) || !isDateInAllowedWindow(endInput.value)) {
                    totalDays.textContent = '-';
                    totalPrice.textContent = 'Rp -';
                    availabilityState = 'invalid';
                    setAddToCartState(false);
                    setAvailabilityMessage(@json(__('Tanggal sewa hanya bisa dipilih dari hari ini sampai 3 bulan ke depan.')), 'error');
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
                    return;
                }

                totalDays.textContent = `${diff} {{ __('app.product.day_label') }}`;
                totalPrice.textContent = formatIDR(price * diff);
            };

            const checkAvailability = async () => {
                if (!availabilityUrl || !startInput.value || !endInput.value) return;
                const qty = Number(qtyInput?.value || '1');
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

                    availabilityState = payload.status || 'unknown';

                    if (availabilityState === 'available') {
                        setAddToCartState(true);
                        setAvailabilityMessage(payload.message || @json(__('ui.availability.available')), 'success');
                        return;
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
                } catch (error) {
                    availabilityState = 'error';
                    setAddToCartState(false);
                    setAvailabilityMessage(error.message || @json(__('ui.availability.not_available')), 'error');
                }
            };

            const scheduleAvailabilityCheck = () => {
                if (!startInput.value || !endInput.value) return;
                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(checkAvailability, 260);
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
                startInput.addEventListener('change', () => {
                    if (startInput.value) endInput.min = startInput.value > minDate ? startInput.value : minDate;
                    else endInput.min = minDate;
                    updateTotal();
                    scheduleAvailabilityCheck();
                });
                endInput.addEventListener('change', () => { updateTotal(); scheduleAvailabilityCheck(); });
            }

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
                rentForm.addEventListener('submit', (event) => {
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
                    if (availabilityState !== 'available') {
                        event.preventDefault();
                        alert(@json(__('ui.availability.not_available')));
                    }
                });
            }
        })();
    </script>
@endsection

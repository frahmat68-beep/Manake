@extends('layouts.app')

@php
    $statusValue = $equipment->status ?? ($equipment->stock > 0 ? 'ready' : 'unavailable');
    $statusKey = $statusValue === 'ready' ? 'ready' : 'rented';
    $statusLabel = $statusKey === 'ready' ? __('app.status.ready') : __('app.status.rented');
    $statusClass = $statusKey === 'ready'
        ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
        : 'bg-amber-500/10 text-amber-600 border-amber-500/20';
    $imagePath = $equipment->image_path ?? $equipment->image;
    $fallbackImage = 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=1200&q=80';
    $mainImage = site_media_url($imagePath) ?: $fallbackImage;
    $gallery = $mainImage ? [$mainImage] : [];
    $reservedUnits = (int) ($equipment->reserved_units ?? 0);
    $availableUnits = (int) $equipment->available_units;
    $canRent = $statusValue === 'ready' && (int) $equipment->stock > 0;
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

    <div class="bg-slate-50 min-h-screen">
        <section class="relative overflow-hidden pt-12 pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
                <div class="glass-lg noise-overlay spotlight-shell rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl animate-fade-up">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="glass-sm px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-blue-600 border border-blue-500/10">
                                    {{ $equipment->category?->name ?? __('app.category.title') }}
                                </span>
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl leading-tight">
                                {{ $equipment->name }}
                            </h1>
                            <p class="mt-4 text-base text-slate-600 sm:text-lg max-w-2xl leading-relaxed">
                                {{ __('app.product.meta') }}
                            </p>
                        </div>
                        <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors bg-white/50 px-6 py-3 rounded-2xl border border-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            {{ __('app.actions.back_to_catalog') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-24">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-[1.1fr,0.9fr]">
                <!-- Left Column: Visual & Specs -->
                <div class="space-y-8">
                    <div 
                        x-data="{
                            handleMouseMove(e) {
                                const rect = $el.getBoundingClientRect();
                                $el.style.setProperty('--x', (e.clientX - rect.left) + 'px');
                                $el.style.setProperty('--y', (e.clientY - rect.top) + 'px');
                            }
                        }"
                        @mousemove="handleMouseMove"
                        class="premium-card spotlight-shell noise-overlay rounded-[2.5rem] p-10 flex items-center justify-center bg-white/40 border border-white/20 shadow-2xl animate-fade-up"
                    >
                        <img
                            src="{{ $mainImage }}"
                            alt="{{ $equipment->name }}"
                            class="h-80 w-full object-contain sm:h-[450px] drop-shadow-2xl transition-transform duration-700 hover:scale-105"
                            onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                        >
                    </div>

                    @if (count($gallery) > 1)
                        <div class="grid grid-cols-4 gap-4 animate-fade-up" style="animation-delay: 100ms">
                            @foreach (array_slice($gallery, 1) as $image)
                                <button class="premium-card noise-overlay rounded-2xl border border-white/20 bg-white/30 p-4 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl group" type="button">
                                    <img
                                        src="{{ $image }}"
                                        alt="Gallery {{ $equipment->name }}"
                                        class="h-20 w-full object-contain transition-transform duration-500 group-hover:scale-110"
                                        onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                                        loading="lazy"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="premium-card noise-overlay rounded-[2.5rem] p-10 border border-white/20 bg-white/40 shadow-xl animate-fade-up" style="animation-delay: 200ms">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="h-12 w-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ __('app.product.specs') }}</h3>
                        </div>

                        @if ($specifications->isEmpty())
                            <div class="bg-slate-50/50 rounded-3xl p-8 text-center border border-slate-100">
                                <p class="text-slate-500 font-medium">{{ __('app.product.spec_unavailable') }}</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @foreach ($specifications as $specification)
                                    <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-white/50 transition-colors duration-300 border border-transparent hover:border-blue-100/50">
                                        <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></div>
                                        <span class="text-[15px] font-semibold text-slate-700 leading-relaxed">{{ $specification }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Pricing & Booking -->
                <div class="space-y-8">
                    <!-- Pricing Card -->
                    <div class="premium-card noise-overlay spotlight-shell rounded-[2.5rem] p-10 border border-white/20 bg-white/40 shadow-2xl animate-fade-up" style="animation-delay: 300ms">
                        <div class="flex flex-col gap-6">
                            <div class="space-y-1">
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600/70">{{ __('app.product.price_per_day') }}</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl font-black tracking-tighter text-slate-950">Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}</span>
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">/ {{ __('app.product.per_day') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white/50 backdrop-blur-md rounded-2xl p-4 border border-white/60 text-center shadow-sm">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('app.product.total_stock') }}</p>
                                    <p class="mt-1 text-xl font-black text-slate-900">{{ $equipment->stock }}</p>
                                </div>
                                <div class="bg-white/50 backdrop-blur-md rounded-2xl p-4 border border-white/60 text-center shadow-sm">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('app.product.in_use') }}</p>
                                    <p class="mt-1 text-xl font-black text-amber-600">{{ $reservedUnits }}</p>
                                </div>
                                <div class="bg-white/50 backdrop-blur-md rounded-2xl p-4 border border-white/60 text-center shadow-sm">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ __('app.product.available_stock') }}</p>
                                    <p class="mt-1 text-xl font-black {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $availableUnits }}</p>
                                </div>
                            </div>

                            <div class="bg-blue-50/50 rounded-3xl p-6 border border-blue-100/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-600">{{ __('app.product.schedule_title') }}</p>
                                </div>
                                
                                @if ($bookingRanges->isEmpty())
                                    <p class="text-sm font-semibold text-slate-500 italic">{{ __('app.product.no_active_schedule') }}</p>
                                @else
                                    <p class="mb-4 text-xs font-medium text-slate-600 leading-relaxed">{{ __('app.product.blocked_schedule_note') }}</p>
                                    <div class="space-y-3">
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
                                                    'buffer_before', 'buffer_after' => 'bg-indigo-500 shadow-indigo-500/50',
                                                    'maintenance' => 'bg-rose-500 shadow-rose-500/50',
                                                    default => 'bg-amber-500 shadow-amber-500/50',
                                                };
                                                $startDate = \Carbon\Carbon::parse($range['start_date'])->translatedFormat('d M Y');
                                                $endDate = \Carbon\Carbon::parse($range['end_date'])->translatedFormat('d M Y');
                                                $dateText = $startDate === $endDate ? $startDate : ($startDate . ' - ' . $endDate);
                                            @endphp
                                            <div class="flex items-center gap-3 p-3 bg-white/60 rounded-xl border border-white shadow-sm">
                                                <div class="h-2 w-2 shrink-0 rounded-full {{ $rangeDotClass }} shadow-sm"></div>
                                                <div class="flex-1">
                                                    <p class="text-[13px] font-bold text-slate-800">{{ $dateText }}</p>
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                                                        {{ $rangeLabel }} 
                                                        @if (($range['qty'] ?? 0) > 0) • Qty {{ $range['qty'] }} @endif
                                                        @if (($range['reason'] ?? null)) • {{ $range['reason'] }} @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <p class="mt-4 text-[10px] font-medium text-slate-400 text-center italic">{{ __('app.product.checkout_reject_note') }}</p>
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
                        class="premium-card noise-overlay rounded-[2.5rem] p-10 border border-white/20 bg-white/40 shadow-2xl animate-fade-up" 
                        style="animation-delay: 400ms"
                    >
                        <h3 class="text-2xl font-black tracking-tight text-slate-900 mb-8">{{ __('app.product.rental_date') }}</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('app.product.start_date') }}</label>
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
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 p-4 text-sm font-bold text-slate-900 focus:ring-4 ring-blue-500/10 transition-all duration-300 {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-500 opacity-60' : '' }}"
                                >
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('app.product.end_date') }}</label>
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
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 p-4 text-sm font-bold text-slate-900 focus:ring-4 ring-blue-500/10 transition-all duration-300 {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-500 opacity-60' : '' }}"
                                >
                            </div>
                        </div>

                        @if ($lockDates)
                            <div class="mb-8 p-4 bg-blue-50 rounded-2xl border border-blue-100 text-[11px] font-bold text-blue-700 flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                {{ __('Tanggal sewa dikunci mengikuti pesanan di cart. Untuk ubah tanggal, edit dulu item di cart.') }}
                            </div>
                        @endif

                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-between rounded-2xl bg-white/50 px-6 py-4 text-sm border border-white/60">
                                <span class="font-bold text-slate-500">{{ __('app.product.duration') }}</span>
                                <span id="total-days" class="text-base font-black text-slate-950">-</span>
                            </div>
                            <div class="flex items-center justify-between rounded-[1.5rem] bg-blue-600 px-6 py-5 text-sm text-white shadow-xl shadow-blue-600/20 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                                <span class="relative font-bold text-white/80">{{ __('app.product.estimate') }}</span>
                                <span id="total-price" class="relative text-2xl font-black">Rp -</span>
                            </div>
                            <div id="availability-feedback" class="hidden rounded-2xl border-2 px-6 py-4 text-xs font-bold leading-relaxed whitespace-pre-line animate-fade-in"></div>
                        </div>

                        <div class="space-y-4">
                            @guest
                                @if ($canRent)
                                    <a
                                        href="{{ route('login', ['reason' => 'cart']) }}"
                                        @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                        class="btn-primary w-full py-4 rounded-2xl font-black tracking-widest uppercase text-xs text-center shadow-blue-600/30"
                                    >
                                        {{ __('ui.actions.login_to_add') }}
                                    </a>
                                @else
                                    <button type="button" disabled class="w-full py-4 rounded-2xl bg-slate-100 text-slate-400 font-bold border border-slate-200 cursor-not-allowed">
                                        {{ __('app.product.out_of_stock') }}
                                    </button>
                                @endif
                                <p class="text-[11px] font-bold text-slate-400 text-center uppercase tracking-widest">{{ __('app.messages.login_to_cart') }}</p>
                            @endguest

                            @auth
                                <form id="rent-form" method="POST" action="{{ route('cart.add') }}" class="space-y-6" x-data="{ qty: 1, maxQty: {{ max((int) $equipment->stock, 1) }} }">
                                    @csrf
                                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
                                    <input type="hidden" name="name" value="{{ $equipment->name }}">
                                    <input type="hidden" name="slug" value="{{ $equipment->slug }}">
                                    <input type="hidden" name="category" value="{{ $equipment->category?->name }}">
                                    <input type="hidden" name="image" value="{{ $mainImage }}">
                                    <input type="hidden" name="price" value="{{ $equipment->price_per_day }}">
                                    
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Kuantitas</label>
                                        <div class="relative flex items-center">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="absolute left-2 h-11 w-11 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all active:scale-90">-</button>
                                            <input
                                                id="rent-qty"
                                                type="number"
                                                name="qty"
                                                min="1"
                                                :max="maxQty"
                                                x-model="qty"
                                                class="no-spinner w-full rounded-2xl border-slate-200 bg-slate-50/50 py-4 text-center text-base font-black text-slate-950 focus:ring-4 ring-blue-500/10 transition-all"
                                                required
                                            >
                                            <button type="button" @click="qty = Math.min(maxQty, qty + 1)" class="absolute right-2 h-11 w-11 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all active:scale-90">+</button>
                                        </div>
                                    </div>

                                    <button
                                        id="add-to-cart-button"
                                        type="submit"
                                        class="btn-primary w-full py-4 rounded-2xl font-black tracking-widest uppercase text-xs shadow-blue-600/30 disabled:opacity-50 disabled:translate-y-0"
                                        @disabled(! $canRent)
                                    >
                                        {{ $canRent ? __('ui.actions.add_to_cart') : __('app.product.out_of_stock') }}
                                    </button>
                                </form>
                                <p class="text-[11px] font-bold text-slate-400 text-center uppercase tracking-widest">{{ __('app.product.note') }}</p>
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
                    info: 'border-blue-100 bg-blue-50/80 text-blue-700',
                    success: 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
                    warning: 'border-amber-100 bg-amber-50/80 text-amber-700',
                    error: 'border-rose-100 bg-rose-50/80 text-rose-700',
                };
                availabilityFeedback.className = `rounded-2xl border-2 px-6 py-4 text-xs font-bold leading-relaxed whitespace-pre-line animate-fade-in ${classes[tone] || classes.info}`;
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

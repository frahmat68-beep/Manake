@extends('layouts.app')

@php
    $statusValue = $equipment->status ?? ($equipment->stock > 0 ? 'ready' : 'unavailable');
    $statusKey = $statusValue === 'ready' ? 'ready' : 'rented';
    $statusLabel = $statusKey === 'ready' ? __('app.status.ready') : __('app.status.rented');
    $statusClass = $statusKey === 'ready'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-amber-100 text-amber-700';
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

    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <div class="catalog-hero rounded-[2rem] p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="section-kicker">{{ $equipment->category?->name ?? __('app.category.title') }}</p>
                        <h1 class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">{{ $equipment->name }}</h1>
                        <p class="text-sm text-slate-600">{{ __('app.product.meta') }}</p>
                    </div>
                    <a href="{{ route('catalog') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600">← {{ __('app.actions.back_to_catalog') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-100">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 pb-14 sm:px-6 lg:grid-cols-[1.05fr,0.95fr] lg:gap-8">
            <div class="space-y-4">
                <div class="media-stage rounded-[1.75rem] p-6 shadow-sm">
                    <img
                        src="{{ $mainImage }}"
                        alt="{{ $equipment->name }}"
                        class="h-72 w-full object-contain sm:h-96 lg:h-[420px]"
                        onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                    >
                </div>
                @if (count($gallery) > 1)
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (array_slice($gallery, 1) as $image)
                            <button class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow" type="button">
                                <img
                                    src="{{ $image }}"
                                    alt="Gallery {{ $equipment->name }}"
                                    class="h-24 w-full object-contain"
                                    onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
                                    loading="lazy"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="surface-band rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('app.product.specs') }}</h3>
                    @if ($specifications->isEmpty())
                        <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-600">
                            <li class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                {{ __('app.product.spec_unavailable') }}
                            </li>
                        </ul>
                    @else
                        <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-600">
                            @foreach ($specifications as $specification)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                    <span>{{ $specification }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="card rounded-[1.75rem] p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">{{ $equipment->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $equipment->category?->name ?? __('app.category.title') }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-semibold text-blue-600">
                        Rp {{ number_format($equipment->price_per_day, 0, ',', '.') }}
                        <span class="text-base font-medium text-slate-500">{{ __('app.product.per_day') }}</span>
                    </p>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="surface-band rounded-lg px-2 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ __('app.product.total_stock') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-900">{{ $equipment->stock }}</p>
                        </div>
                        <div class="surface-band rounded-lg px-2 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ __('app.product.in_use') }}</p>
                            <p class="mt-1 text-base font-semibold text-amber-600">{{ $reservedUnits }}</p>
                        </div>
                        <div class="surface-band rounded-lg px-2 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ __('app.product.available_stock') }}</p>
                            <p class="mt-1 text-base font-semibold {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $availableUnits }}</p>
                        </div>
                    </div>

                    <div class="surface-band mt-4 rounded-xl p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('app.product.schedule_title') }}</p>
                        @if ($bookingRanges->isEmpty())
                            <p class="mt-2 text-sm text-slate-600">{{ __('app.product.no_active_schedule') }}</p>
                        @else
                            <p class="mt-2 text-sm text-slate-600">{{ __('app.product.blocked_schedule_note') }}</p>
                            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                @foreach ($bookingRanges as $range)
                                    @php
                                        $rangeType = $range['type'] ?? 'booking';
                                        $isCurrentUserSchedule = (bool) ($range['is_current_user'] ?? false);
                                        $rangeLabel = match ($rangeType) {
                                            'buffer_before', 'buffer_after' => $isCurrentUserSchedule
                                                ? __('app.product.my_buffer_label')
                                                : __('app.product.buffer_label'),
                                            'maintenance' => __('app.product.maintenance_label'),
                                            'booking' => $isCurrentUserSchedule
                                                ? __('app.product.my_booking_label')
                                                : __('app.product.booked_label'),
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
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1 h-2 w-2 rounded-full {{ $rangeDotClass }}"></span>
                                        <span>
                                            {{ $dateText }} • {{ $rangeLabel }}
                                            @if (($range['qty'] ?? 0) > 0)
                                                • Qty {{ $range['qty'] }}
                                            @endif
                                            @if (($range['reason'] ?? null))
                                                • {{ $range['reason'] }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="mt-2 text-xs text-slate-500">{{ __('app.product.checkout_reject_note') }}</p>
                    </div>

                </div>

                <div
                    class="card space-y-4 rounded-[1.75rem] p-6 shadow-sm"
                    id="rental-summary"
                    data-price="{{ $equipment->price_per_day }}"
                    data-availability-url="{{ $availabilityEndpoint }}"
                    data-min-date="{{ $bookingMinDate }}"
                    data-max-date="{{ $bookingMaxDate }}"
                    data-lock-dates="{{ $lockDates ? '1' : '0' }}"
                    data-locked-start="{{ $prefillStartDate }}"
                    data-locked-end="{{ $prefillEndDate }}"
                >
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('app.product.rental_date') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500">{{ __('app.product.start_date') }}</label>
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
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-500' : '' }}"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">{{ __('app.product.end_date') }}</label>
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
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none {{ $lockDates ? 'cursor-not-allowed bg-slate-100 text-slate-500' : '' }}"
                            >
                        </div>
                    </div>
                    @if ($lockDates)
                        <p class="text-xs text-blue-700">
                            {{ __('Tanggal sewa dikunci mengikuti pesanan di cart. Untuk ubah tanggal, edit dulu item di cart.') }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <span>{{ __('app.product.duration') }}</span>
                        <span id="total-days" class="font-semibold text-slate-900">-</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <span>{{ __('app.product.estimate') }}</span>
                        <span id="total-price" class="text-base font-semibold text-slate-900">Rp -</span>
                    </div>
                    <div id="availability-feedback" class="hidden rounded-xl border px-4 py-3 text-xs"></div>

                    @guest
                        @if ($canRent)
                            <a
                                href="{{ route('login', ['reason' => 'cart']) }}"
                                @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition"
                            >
                                {{ __('ui.actions.login_to_add') }}
                            </a>
                        @else
                            <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-300 px-4 py-3 text-sm font-semibold text-slate-500">
                                {{ __('app.product.out_of_stock') }}
                            </button>
                        @endif
                        <p class="text-xs text-slate-400 text-center">{{ __('app.messages.login_to_cart') }}</p>
                    @endguest
                    @auth
                        <form id="rent-form" method="POST" action="{{ route('cart.add') }}" class="space-y-3" x-data="{ qty: 1, maxQty: {{ max((int) $equipment->stock, 1) }} }">
                            @csrf
                            <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
                            <input type="hidden" name="name" value="{{ $equipment->name }}">
                            <input type="hidden" name="slug" value="{{ $equipment->slug }}">
                            <input type="hidden" name="category" value="{{ $equipment->category?->name }}">
                            <input type="hidden" name="image" value="{{ $mainImage }}">
                            <input type="hidden" name="price" value="{{ $equipment->price_per_day }}">
                            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                                <button type="button" class="h-8 w-8 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:text-blue-600" @click="qty = Math.max(1, qty - 1)">-</button>
                                <input
                                    id="rent-qty"
                                type="number"
                                name="qty"
                                min="1"
                                :max="maxQty"
                                x-model="qty"
                                class="no-spinner w-12 bg-transparent text-center text-sm font-semibold text-slate-700 focus:outline-none"
                            >
                                <button type="button" class="h-8 w-8 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:text-blue-600" @click="qty = Math.min(maxQty, qty + 1)">+</button>
                            </div>
                            <button
                                id="add-to-cart-button"
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition"
                                @disabled(! $canRent)
                            >
                                {{ $canRent ? __('ui.actions.add_to_cart') : __('app.product.out_of_stock') }}
                            </button>
                        </form>
                        <p class="text-xs text-slate-400 text-center">{{ __('app.product.note') }}</p>
                    @endauth
                </div>
            </div>
        </div>
    </section>

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
                if (!value) {
                    return false;
                }

                if (minDate && value < minDate) {
                    return false;
                }

                if (maxDate && value > maxDate) {
                    return false;
                }

                return true;
            };

            const setAddToCartState = (enabled) => {
                if (!addToCartButton) {
                    return;
                }

                addToCartButton.disabled = !enabled;
                addToCartButton.classList.toggle('opacity-50', !enabled);
                addToCartButton.classList.toggle('cursor-not-allowed', !enabled);
            };

            const setAvailabilityMessage = (message, tone = 'info') => {
                if (!availabilityFeedback) {
                    return;
                }

                const classes = {
                    info: 'border-slate-200 bg-slate-50 text-slate-700',
                    success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    warning: 'border-amber-200 bg-amber-50 text-amber-700',
                    error: 'border-rose-200 bg-rose-50 text-rose-700',
                };

                availabilityFeedback.className = `rounded-xl border px-4 py-3 text-xs leading-relaxed whitespace-pre-line ${classes[tone] || classes.info}`;
                availabilityFeedback.textContent = message;
                availabilityFeedback.classList.remove('hidden');
            };

            const clearAvailabilityMessage = () => {
                if (!availabilityFeedback) {
                    return;
                }

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
                if (!availabilityUrl || !startInput.value || !endInput.value) {
                    return;
                }

                const qty = Number(qtyInput?.value || '1');
                setAvailabilityMessage(@json(__('app.product.checking_availability')), 'info');

                try {
                    const params = new URLSearchParams({
                        start_date: startInput.value,
                        end_date: endInput.value,
                        qty: String(Number.isNaN(qty) ? 1 : Math.max(qty, 1)),
                    });
                    const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || @json(__('ui.availability.not_available')));
                    }

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
                    if (conflicts.length > 0) {
                        lines.push(`{{ __('app.product.availability_conflict_label') }} ${conflicts.slice(0, 4).join(', ')}`);
                    }
                    if (suggestions.length > 0) {
                        lines.push(`{{ __('app.product.availability_suggestions_label') }} ${suggestions.join(' | ')}`);
                    }
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
                if (!startInput.value || !endInput.value) {
                    return;
                }

                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                debounceTimer = window.setTimeout(() => {
                    checkAvailability();
                }, 260);
            };

            const applyDateInputLimits = () => {
                if (!startInput || !endInput) {
                    return;
                }

                if (minDate) {
                    startInput.min = minDate;
                    endInput.min = minDate;
                }
                if (maxDate) {
                    startInput.max = maxDate;
                    endInput.max = maxDate;
                }

                if (startInput.value) {
                    endInput.min = startInput.value > minDate ? startInput.value : minDate;
                }
            };

            const enforceLockedDates = () => {
                if (!isLockedDates || !startInput || !endInput) {
                    return;
                }

                if (lockedStartDate) {
                    startInput.value = lockedStartDate;
                }
                if (lockedEndDate) {
                    endInput.value = lockedEndDate;
                }

                const keepLockedValue = (input, expected) => {
                    if (!input) return;
                    input.addEventListener('input', () => {
                        if (expected) {
                            input.value = expected;
                        }
                    });
                    input.addEventListener('change', () => {
                        if (expected) {
                            input.value = expected;
                        }
                    });
                    input.addEventListener('keydown', (event) => {
                        event.preventDefault();
                    });
                    input.setAttribute('aria-readonly', 'true');
                };

                keepLockedValue(startInput, lockedStartDate);
                keepLockedValue(endInput, lockedEndDate);
            };

            if (startInput && endInput) {
                startInput.addEventListener('change', () => {
                    if (startInput.value) {
                        endInput.min = startInput.value > minDate ? startInput.value : minDate;
                    } else {
                        endInput.min = minDate;
                    }
                    updateTotal();
                    scheduleAvailabilityCheck();
                });

                endInput.addEventListener('change', () => {
                    updateTotal();
                    scheduleAvailabilityCheck();
                });
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
                if (startInput.value && endInput.value) {
                    scheduleAvailabilityCheck();
                }
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

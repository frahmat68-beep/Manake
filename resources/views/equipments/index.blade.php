@extends('layouts.app')

@section('title', __('app.catalog.title'))

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
        .idle-hamburger-indicator {
            animation: catalog-idle-hamburger-drop 900ms ease-in-out infinite;
        }
        @keyframes catalog-idle-hamburger-drop {
            0% {
                transform: translateY(-80%);
                opacity: 0.25;
            }
            55% {
                transform: translateY(-35%);
                opacity: 1;
            }
            100% {
                transform: translateY(2%);
                opacity: 0.3;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $groups = collect($groupedEquipments ?? []);
        $categories = collect($categories ?? []);
        $activeCategorySlug = $activeCategorySlug ?? '';
        $search = trim((string) ($search ?? request('q', '')));
        $catalogTitle = setting('copy.catalog.title', __('app.catalog.title'));
        $catalogSubtitle = setting('copy.catalog.subtitle', __('app.catalog.subtitle'));
        $categoryLabel = setting('copy.catalog.category_label', __('app.catalog.filter_category'));
        $emptyTitle = setting('copy.catalog.empty_title', __('ui.catalog.empty_title'));
        $emptySubtitle = setting('copy.catalog.empty_subtitle', __('ui.catalog.empty_subtitle'));
        $intlLocale = app()->getLocale() === 'en' ? 'en-US' : 'id-ID';
        $currencyPrefix = app()->getLocale() === 'en' ? 'IDR' : 'Rp';
        $dayLabel = __('app.product.day_label');
        $catalogResetSearchLabel = __('ui.catalog.reset_search_label');
        $catalogAllCategoriesLabel = __('ui.catalog.all_categories_label');
        $catalogSearchResultPrefix = __('ui.catalog.search_result_prefix');
        $catalogItemSuffix = __('ui.catalog.item_suffix');
        $catalogStockLabel = __('ui.catalog.stock_label');
        $catalogInUseLabel = __('ui.catalog.in_use_label');
        $catalogAvailableLabel = __('ui.catalog.available_label');
        $catalogAvailabilityNote = __('ui.catalog.availability_note');
        $catalogFallbackImage = 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=900&q=80';
        $catalogQuickOrderButton = __('ui.catalog.quick_order_button');
        $catalogQuickOrderTitle = __('ui.catalog.quick_order_title');
        $catalogQuickOrderHint = __('ui.catalog.quick_order_hint');
        $catalogQuickStartDateLabel = __('ui.catalog.quick_start_date_label');
        $catalogQuickEndDateLabel = __('ui.catalog.quick_end_date_label');
        $catalogQuickQtyLabel = __('ui.catalog.quick_qty_label');
        $catalogQuickDurationLabel = __('ui.catalog.quick_duration_label');
        $catalogQuickEstimateLabel = __('ui.catalog.quick_estimate_label');
        $catalogQuickCancelButton = __('ui.catalog.quick_cancel_button');
        $catalogQuickAddButton = __('ui.catalog.quick_add_button');
        $catalogLoginToOrderButton = __('ui.catalog.login_to_order_button');
        $catalogOutOfStockButton = __('ui.catalog.out_of_stock_button');
        $bookingMinDate = now()->toDateString();
        $bookingMaxDate = now()->addMonthsNoOverflow(3)->toDateString();
        $idleHamburgerEnabledRaw = strtolower(trim((string) setting('catalog.idle_hamburger_enabled', '1')));
        $idleHamburgerEnabled = ! in_array($idleHamburgerEnabledRaw, ['0', 'false', 'off', 'no', 'tidak'], true);
        $idleHamburgerDelayMs = max(1000, min(12000, (int) setting('catalog.idle_hamburger_delay_ms', 2200)));
        $idleHamburgerStepMs = max(500, min(4000, (int) setting('catalog.idle_hamburger_step_ms', 900)));
    @endphp

    <div
        x-data="catalogIdleHamburger({
            enabled: @js($idleHamburgerEnabled),
            delay: @js($idleHamburgerDelayMs),
            step: @js($idleHamburgerStepMs),
            total: @js($categories->count()),
        })"
        x-init="init()"
        @mouseenter="markPointerEnter"
        @mousemove.passive="markPointerMove"
        @mouseleave="markPointerLeave"
        @touchstart.passive="stopGuide"
    >
        <section class="bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
                <div class="catalog-hero rounded-[2rem] p-5 sm:p-6">
                    <div class="flex flex-col gap-3">
                        <div class="max-w-3xl">
                            <p class="section-kicker">{{ __('ui.nav.catalog') }}</p>
                            <h1 class="mt-2 text-2xl font-extrabold text-blue-700 sm:text-3xl">{{ $catalogTitle }}</h1>
                            <p class="mt-2 text-sm text-slate-600 sm:text-base">{{ $catalogSubtitle }}</p>
                        </div>
                    </div>

                    <div class="surface-band mt-5 rounded-2xl p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs font-semibold text-slate-500">{{ $categoryLabel }}</p>
                            @if ($search !== '')
                                <a href="{{ route('catalog', $activeCategorySlug !== '' ? ['category' => $activeCategorySlug] : []) }}" class="btn-secondary inline-flex items-center justify-center rounded-xl px-3 py-1.5 text-xs font-semibold transition">
                                    {{ $catalogResetSearchLabel }}
                                </a>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                href="{{ route('catalog', $search !== '' ? ['q' => $search] : []) }}"
                                class="catalog-filter-chip rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $activeCategorySlug === '' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200 hover:text-blue-600' }}"
                            >
                                {{ $catalogAllCategoriesLabel }}
                            </a>
                            @foreach ($categories as $category)
                                @php
                                    $categoryParams = ['category' => $category->slug];
                                    if ($search !== '') {
                                        $categoryParams['q'] = $search;
                                    }
                                @endphp
                                <a
                                    href="{{ route('catalog', $categoryParams) }}"
                                    class="catalog-filter-chip relative rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $activeCategorySlug === $category->slug ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200 hover:text-blue-600' }}"
                                    :class="showGuide && activeGuideIndex === {{ $loop->iteration }} ? 'pr-8 border-blue-300 text-blue-700 shadow-sm' : ''"
                                >
                                    {{ $category->name }}
                                    <span
                                        x-cloak
                                        x-show="showGuide && activeGuideIndex === {{ $loop->iteration }}"
                                        x-transition.opacity.duration.200ms
                                        class="idle-hamburger-indicator pointer-events-none absolute right-2 top-1/2 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                            <line x1="5" y1="7" x2="19" y2="7"></line>
                                            <line x1="8" y1="12" x2="16" y2="12"></line>
                                            <line x1="10" y1="17" x2="14" y2="17"></line>
                                        </svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($search !== '')
                    <p class="mt-4 text-sm text-slate-500">
                        {{ $catalogSearchResultPrefix }} <span class="font-semibold text-slate-700">&quot;{{ $search }}&quot;</span>.
                    </p>
                @endif
            </div>
        </section>

        <section class="bg-slate-100">
            <div class="mx-auto max-w-7xl space-y-10 px-4 pb-12 sm:px-6">
                @forelse ($groups as $group)
                    @php
                        $category = $group['category'];
                        $items = collect($group['items'] ?? []);
                    @endphp

                    <section>
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-blue-700">{{ $category->name }}</h2>
                                @if (!empty($category->description))
                                    <p class="text-sm text-slate-500">{{ $category->description }}</p>
                                @endif
                            </div>
                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ $items->count() }} {{ $catalogItemSuffix }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $item)
                            @php
                                $statusValue = $item->status ?? ($item->stock > 0 ? 'ready' : 'unavailable');
                                $statusKey = $statusValue === 'ready' ? 'ready' : 'rented';
                                $statusLabel = $statusKey === 'ready' ? __('app.status.ready') : __('app.status.rented');
                                $statusClass = $statusKey === 'ready'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-amber-100 text-amber-700';
                                $imagePath = $item->image_path ?? $item->image;
                                $image = site_media_url($imagePath) ?: $catalogFallbackImage;
                                $prioritizeImage = ($loop->parent?->first ?? false) && $loop->index < 3;
                                $reservedUnits = (int) ($item->reserved_units ?? 0);
                                $availableUnits = (int) $item->available_units;
                                $canRent = $statusValue === 'ready' && (int) $item->stock > 0;
                            @endphp

                            <article
                                x-data="{
                                    quickOpen: false,
                                    quickQty: 1,
                                    quickStart: '',
                                    quickEnd: '',
                                    minDate: '{{ $bookingMinDate }}',
                                    maxDate: '{{ $bookingMaxDate }}',
                                    maxQty: {{ max((int) $item->stock, 1) }},
                                    calcDays() {
                                        if (!this.quickStart || !this.quickEnd) return 0;
                                        const start = new Date(this.quickStart);
                                        const end = new Date(this.quickEnd);
                                        const diff = Math.ceil((end - start) / 86400000) + 1;
                                        return Number.isNaN(diff) || diff <= 0 ? 0 : diff;
                                    },
                                    calcTotal() {
                                        const days = this.calcDays();
                                        return days > 0 ? days * {{ (int) $item->price_per_day }} * this.quickQty : 0;
                                    },
                                    formatIdr(value) {
                                        return new Intl.NumberFormat(@js($intlLocale)).format(value);
                                    }
                                }"
                                class="card group flex h-full flex-col overflow-hidden rounded-[1.6rem] shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <div class="media-stage relative flex h-56 items-center justify-center p-4 sm:h-60">
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $item->name }}"
                                        class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                        onerror="this.onerror=null;this.src='{{ $catalogFallbackImage }}';"
                                        loading="{{ $prioritizeImage ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $prioritizeImage ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                    <span class="badge-status absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold">
                                        {{ $item->category?->name ?? __('app.category.title') }}
                                    </span>
                                    <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <h3 class="min-h-[3.4rem] text-lg font-semibold leading-snug text-slate-900">{{ $item->name }}</h3>
                                    <p class="mt-2 text-xs text-slate-500">{{ __('app.product.price_per_day') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $currencyPrefix }} {{ number_format($item->price_per_day, 0, ',', '.') }}</p>

                                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                        <div class="surface-band rounded-lg px-2 py-2">
                                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ $catalogStockLabel }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $item->stock }}</p>
                                        </div>
                                        <div class="surface-band rounded-lg px-2 py-2">
                                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ $catalogInUseLabel }}</p>
                                            <p class="mt-1 text-sm font-semibold text-amber-600">{{ $reservedUnits }}</p>
                                        </div>
                                        <div class="surface-band rounded-lg px-2 py-2">
                                            <p class="text-[10px] uppercase tracking-wide text-slate-500">{{ $catalogAvailableLabel }}</p>
                                            <p class="mt-1 text-sm font-semibold {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $availableUnits }}</p>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-[11px] text-slate-500">{{ $catalogAvailabilityNote }}</p>

                                    <div class="mt-4 mt-auto space-y-3">
                                        <a
                                            href="{{ route('product.show', $item->slug) }}"
                                            class="btn-secondary inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                                        >
                                            {{ __('app.actions.view_detail') }}
                                        </a>

                                        @auth
                                            @if ($canRent)
                                                <button
                                                    type="button"
                                                    class="btn-primary inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                                                    @click="quickOpen = true; quickQty = 1; quickStart = ''; quickEnd = '';"
                                                >
                                                    {{ $catalogQuickOrderButton }}
                                                </button>
                                            @else
                                                <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-500">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endauth

                                        @guest
                                            @if ($canRent)
                                                <a
                                                    href="{{ route('login', ['reason' => 'cart']) }}"
                                                    @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                                    class="btn-primary inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                                                >
                                                    {{ $catalogLoginToOrderButton }}
                                                </a>
                                            @else
                                                <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-500">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endguest
                                    </div>
                                </div>

                                @auth
                                    @if ($canRent)
                                        <template x-teleport="body">
                                            <div
                                                x-cloak
                                                x-show="quickOpen"
                                                x-transition.opacity
                                                class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/45 px-4"
                                                @click.self="quickOpen = false"
                                                @keydown.escape.window="quickOpen = false"
                                            >
                                                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-xs font-semibold text-blue-600">{{ $catalogQuickOrderTitle }}</p>
                                                            <h4 class="mt-1 text-base font-semibold text-slate-900">{{ $item->name }}</h4>
                                                            <p class="text-xs text-slate-500">{{ $catalogQuickOrderHint }}</p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:text-slate-700"
                                                            @click="quickOpen = false"
                                                            aria-label="{{ __('ui.actions.close') }}"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <form method="POST" action="{{ route('cart.add') }}" class="mt-4 space-y-3">
                                                        @csrf
                                                        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                                        <input type="hidden" name="name" value="{{ $item->name }}">
                                                        <input type="hidden" name="slug" value="{{ $item->slug }}">
                                                        <input type="hidden" name="category" value="{{ $item->category?->name }}">
                                                        <input type="hidden" name="image" value="{{ $image }}">
                                                        <input type="hidden" name="price" value="{{ $item->price_per_day }}">

                                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label class="text-xs font-semibold text-slate-500">{{ $catalogQuickStartDateLabel }}</label>
                                                                <input
                                                                    type="date"
                                                                    name="rental_start_date"
                                                                    x-model="quickStart"
                                                                    min="{{ $bookingMinDate }}"
                                                                    max="{{ $bookingMaxDate }}"
                                                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                                                    required
                                                                >
                                                            </div>
                                                            <div>
                                                                <label class="text-xs font-semibold text-slate-500">{{ $catalogQuickEndDateLabel }}</label>
                                                                <input
                                                                    type="date"
                                                                    name="rental_end_date"
                                                                    x-model="quickEnd"
                                                                    :min="quickStart || minDate"
                                                                    :max="maxDate"
                                                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-500">{{ $catalogQuickQtyLabel }}</label>
                                                            <input
                                                                type="number"
                                                                name="qty"
                                                                min="1"
                                                                :max="maxQty"
                                                                x-model="quickQty"
                                                                class="no-spinner mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                                                required
                                                            >
                                                        </div>

                                                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                                            <div>
                                                                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $catalogQuickDurationLabel }}</p>
                                                                <p class="font-semibold text-slate-900" x-text="calcDays() > 0 ? `${calcDays()} {{ $dayLabel }}` : '-'"></p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ $catalogQuickEstimateLabel }}</p>
                                                                <p class="font-semibold text-slate-900" x-text="calcTotal() > 0 ? `{{ $currencyPrefix }} ${formatIdr(calcTotal())}` : '{{ $currencyPrefix }} -'"></p>
                                                            </div>
                                                        </div>

                                                        <div class="flex gap-2 pt-1">
                                                            <button type="button" class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600" @click="quickOpen = false">
                                                                {{ $catalogQuickCancelButton }}
                                                            </button>
                                                            <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                                                {{ $catalogQuickAddButton }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    @endif
                                @endauth
                            </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="card rounded-2xl p-8 text-center shadow-sm">
                        <p class="text-base font-semibold text-slate-900">{{ $emptyTitle }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $emptySubtitle }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('catalogIdleHamburger', (config = {}) => ({
                enabled: Boolean(config.enabled),
                delay: Number(config.delay) || 2200,
                step: Number(config.step) || 900,
                total: Number(config.total) || 0,
                supportsHover: window.matchMedia ? window.matchMedia('(hover: hover) and (pointer: fine)').matches : true,
                pointerInside: false,
                showGuide: false,
                activeGuideIndex: 1,
                idleTimer: null,
                cycleTimer: null,
                init() {
                    if (! this.canRun()) {
                        return;
                    }
                },
                canRun() {
                    return this.enabled && this.total > 0 && this.supportsHover;
                },
                markPointerEnter() {
                    if (! this.canRun()) {
                        return;
                    }
                    this.pointerInside = true;
                    this.resetIdleCycle();
                },
                markPointerMove() {
                    if (! this.pointerInside || ! this.canRun()) {
                        return;
                    }
                    this.resetIdleCycle();
                },
                markPointerLeave() {
                    this.pointerInside = false;
                    this.stopGuide();
                },
                resetIdleCycle() {
                    this.stopGuide();
                    this.idleTimer = window.setTimeout(() => {
                        if (! this.pointerInside || ! this.canRun()) {
                            return;
                        }
                        this.startGuide();
                    }, this.delay);
                },
                startGuide() {
                    this.showGuide = true;
                    this.activeGuideIndex = 1;
                    this.cycleTimer = window.setInterval(() => {
                        this.activeGuideIndex = this.activeGuideIndex >= this.total
                            ? 1
                            : this.activeGuideIndex + 1;
                    }, this.step);
                },
                stopGuide() {
                    this.showGuide = false;
                    this.activeGuideIndex = 1;
                    if (this.idleTimer) {
                        clearTimeout(this.idleTimer);
                        this.idleTimer = null;
                    }
                    if (this.cycleTimer) {
                        clearInterval(this.cycleTimer);
                        this.cycleTimer = null;
                    }
                },
            }));
        });
    </script>
@endpush

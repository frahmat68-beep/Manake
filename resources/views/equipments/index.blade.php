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
            enabled: {{ $idleHamburgerEnabled ? 'true' : 'false' }},
            delay: {{ (int) $idleHamburgerDelayMs }},
            step: {{ (int) $idleHamburgerStepMs }},
            total: {{ (int) $categories->count() }},
        })"
        x-init="init()"
        @mouseenter="markPointerEnter"
        @mousemove.passive="markPointerMove"
        @mouseleave="markPointerLeave"
        @touchstart.passive="stopGuide"
        class="bg-slate-50 min-h-screen"
    >
        <section class="relative overflow-hidden pt-12 pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
                <div class="glass-lg noise-overlay spotlight-shell rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl animate-fade-up">
                    <div class="flex flex-col gap-3">
                        <div class="max-w-3xl">
                            <p class="section-kicker font-bold tracking-widest uppercase text-blue-600/80">{{ __('ui.nav.catalog') }}</p>
                            <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-5xl leading-tight">
                                {{ $catalogTitle }}
                            </h1>
                            <p class="mt-4 text-base text-slate-600 sm:text-lg max-w-2xl leading-relaxed">
                                {{ $catalogSubtitle }}
                            </p>
                        </div>
                    </div>

                    <!-- Modern Glass Search Bar -->
                    <div class="max-w-2xl mt-10">
                        <form action="{{ route('catalog') }}" method="GET" class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-500 rounded-[1.5rem] blur opacity-25 group-focus-within:opacity-50 transition duration-500"></div>
                            <div class="relative flex items-center overflow-hidden rounded-[1.35rem] border border-white/40 bg-white/80 backdrop-blur-xl shadow-xl transition-all duration-300 group-focus-within:ring-2 ring-blue-500/20">
                                <span class="pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text" name="q" value="{{ $search }}" placeholder="Cari kamera, lighting, drone, audio..." 
                                       class="flex-1 bg-transparent py-5 pl-14 pr-6 text-[16px] font-medium text-slate-900 border-none focus:ring-0 placeholder:text-slate-400">
                                <button type="submit" class="mr-3 flex h-12 px-6 items-center justify-center rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:scale-[1.03] active:scale-[0.98] transition-all">
                                    <span>Cari</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-10 pt-8 border-t border-slate-200/50">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ $categoryLabel }}</p>
                            @if ($search !== '')
                                <a href="{{ route('catalog', $activeCategorySlug !== '' ? ['category' => $activeCategorySlug] : []) }}" class="btn-secondary rounded-xl px-4 py-2 text-xs font-bold transition">
                                    {{ $catalogResetSearchLabel }}
                                </a>
                            @endif
                        </div>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a
                                href="{{ route('catalog', $search !== '' ? ['q' => $search] : []) }}"
                                class="rounded-full border px-6 py-2 text-xs font-bold tracking-tight transition-all duration-300 {{ $activeCategorySlug === '' ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/20' : 'bg-white/50 text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600' }}"
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
                                    class="relative rounded-full border px-6 py-2 text-xs font-bold tracking-tight transition-all duration-300 {{ $activeCategorySlug === $category->slug ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/20' : 'bg-white/50 text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600' }}"
                                    :class="showGuide && activeGuideIndex === {{ $loop->iteration }} ? 'pr-12 ring-4 ring-blue-500/10 border-blue-400' : ''"
                                >
                                    {{ $category->name }}
                                    <span
                                        x-cloak
                                        x-show="showGuide && activeGuideIndex === {{ $loop->iteration }}"
                                        x-transition.opacity.duration.300ms
                                        class="idle-hamburger-indicator pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-blue-500"
                                        aria-hidden="true"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
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
                    <div class="mt-8 px-4 py-3 bg-blue-50 rounded-2xl border border-blue-100/50 inline-flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                        <p class="text-sm font-medium text-blue-700">
                            {{ $catalogSearchResultPrefix }} <span class="font-bold underline decoration-blue-500/30">&quot;{{ $search }}&quot;</span>
                        </p>
                    </div>
                @endif
            </div>
        </section>

        <section class="pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                @forelse ($groups as $group)
                    @php
                        $category = $group['category'];
                        $items = collect($group['items'] ?? []);
                    @endphp

                    <div class="mb-16">
                        <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between border-b border-slate-200/60 pb-6">
                            <div>
                                <h2 class="text-3xl font-bold tracking-tight text-slate-900">{{ $category->name }}</h2>
                                @if (!empty($category->description))
                                    <p class="mt-2 text-sm text-slate-500 font-medium max-w-2xl">{{ $category->description }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-xs font-bold text-slate-600 border border-slate-200 shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                {{ $items->count() }} {{ $catalogItemSuffix }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $item)
                            @php
                                $statusValue = $item->status ?? ($item->stock > 0 ? 'ready' : 'unavailable');
                                $statusKey = $statusValue === 'ready' ? 'ready' : 'rented';
                                $statusLabel = $statusKey === 'ready' ? __('app.status.ready') : __('app.status.rented');
                                $statusClass = $statusKey === 'ready'
                                    ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
                                    : 'bg-amber-500/10 text-amber-600 border-amber-500/20';
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
                                        return new Intl.NumberFormat('{{ $intlLocale }}').format(value);
                                    },
                                    handleMouseMove(e) {
                                        const rect = $el.getBoundingClientRect();
                                        $el.style.setProperty('--x', (e.clientX - rect.left) + 'px');
                                        $el.style.setProperty('--y', (e.clientY - rect.top) + 'px');
                                    }
                                }"
                                @mousemove="handleMouseMove"
                                @click="if (!$event.target.closest('button, a')) window.location.assign('{{ route('product.show', $item->slug) }}')"
                                class="premium-card spotlight-shell noise-overlay group flex h-full flex-col overflow-hidden rounded-[2rem] transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/10 cursor-pointer animate-fade-up"
                                style="animation-delay: {{ $loop->index * 50 }}ms"
                            >
                                <div class="relative aspect-[4/3] overflow-hidden bg-slate-50/50 p-6 flex items-center justify-center">
                                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $item->name }}"
                                        class="h-full w-full object-contain transition-all duration-700 group-hover:scale-110 drop-shadow-xl"
                                        onerror="this.onerror=null;this.src='{{ $catalogFallbackImage }}';"
                                        loading="{{ $prioritizeImage ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $prioritizeImage ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                    <div class="absolute inset-x-4 top-4 flex items-center justify-between pointer-events-none">
                                        <span class="glass-sm px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-blue-600 border border-blue-500/10">
                                            {{ $item->category?->name ?? __('app.category.title') }}
                                        </span>
                                        <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest border {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col p-7">
                                    <h3 class="text-xl font-bold leading-tight text-slate-900 group-hover:text-blue-600 transition-colors duration-300 min-h-[3.5rem]">{{ $item->name }}</h3>
                                    
                                    <div class="mt-4 flex items-baseline gap-2">
                                        <p class="text-2xl font-black tracking-tight text-slate-950">
                                            {{ $currencyPrefix }} {{ number_format($item->price_per_day, 0, ',', '.') }}
                                        </p>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">/ {{ __('app.product.per_day') }}</span>
                                    </div>

                                    <div class="mt-6 grid grid-cols-3 gap-3">
                                        <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-100 text-center">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $catalogStockLabel }}</p>
                                            <p class="mt-1 text-base font-black text-slate-900">{{ $item->stock }}</p>
                                        </div>
                                        <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-100 text-center">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $catalogInUseLabel }}</p>
                                            <p class="mt-1 text-base font-black text-amber-600">{{ $reservedUnits }}</p>
                                        </div>
                                        <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-100 text-center">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">{{ $catalogAvailableLabel }}</p>
                                            <p class="mt-1 text-base font-black {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $availableUnits }}</p>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-[10px] font-medium text-slate-400 text-center italic opacity-80">{{ $catalogAvailabilityNote }}</p>

                                    <div class="mt-8 flex flex-col gap-3">
                                        @auth
                                            @if ($canRent)
                                                <button
                                                    type="button"
                                                    class="btn-primary w-full py-3.5 rounded-xl font-bold tracking-wide"
                                                    @click="quickOpen = true; quickQty = 1; quickStart = ''; quickEnd = '';"
                                                >
                                                    {{ $catalogQuickOrderButton }}
                                                </button>
                                            @else
                                                <button type="button" disabled class="w-full py-3.5 rounded-xl bg-slate-100 text-slate-400 font-bold border border-slate-200 cursor-not-allowed">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endauth

                                        @guest
                                            @if ($canRent)
                                                <a
                                                    href="{{ route('login', ['reason' => 'cart']) }}"
                                                    @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                                    class="btn-primary w-full py-3.5 rounded-xl font-bold tracking-wide text-center"
                                                >
                                                    {{ $catalogLoginToOrderButton }}
                                                </a>
                                            @else
                                                <button type="button" disabled class="w-full py-3.5 rounded-xl bg-slate-100 text-slate-400 font-bold border border-slate-200 cursor-not-allowed">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endguest

                                        <a
                                            href="{{ route('product.show', $item->slug) }}"
                                            class="btn-secondary w-full py-3 rounded-xl font-bold tracking-wide text-center"
                                        >
                                            {{ __('app.actions.view_detail') }}
                                        </a>
                                    </div>
                                </div>

                                @auth
                                    @if ($canRent)
                                        <template x-teleport="body">
                                            <div
                                                x-cloak
                                                x-show="quickOpen"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
                                                @click.self="quickOpen = false"
                                                @keydown.escape.window="quickOpen = false"
                                            >
                                                <div class="premium-card noise-overlay w-full max-w-lg rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl overflow-hidden relative">
                                                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 blur-[60px] rounded-full"></div>
                                                    
                                                    <div class="relative flex items-start justify-between gap-6">
                                                        <div class="flex-1">
                                                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-full border border-blue-100 mb-3">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                                <span class="text-[10px] font-bold uppercase tracking-widest text-blue-600">{{ $catalogQuickOrderTitle }}</span>
                                                            </div>
                                                            <h4 class="text-2xl font-black tracking-tight text-slate-950">{{ $item->name }}</h4>
                                                            <p class="mt-2 text-sm font-medium text-slate-500 leading-relaxed">{{ $catalogQuickOrderHint }}</p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-50 border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-300 transition-all duration-300 active:scale-90"
                                                            @click="quickOpen = false"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </button>
                                                    </div>

                                                    <form method="POST" action="{{ route('cart.add') }}" class="mt-10 space-y-6 relative">
                                                        @csrf
                                                        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                                        <input type="hidden" name="name" value="{{ $item->name }}">
                                                        <input type="hidden" name="slug" value="{{ $item->slug }}">
                                                        <input type="hidden" name="category" value="{{ $item->category?->name }}">
                                                        <input type="hidden" name="image" value="{{ $image }}">
                                                        <input type="hidden" name="price" value="{{ $item->price_per_day }}">

                                                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                                            <div class="space-y-2">
                                                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ $catalogQuickStartDateLabel }}</label>
                                                                <input
                                                                    type="date"
                                                                    name="rental_start_date"
                                                                    x-model="quickStart"
                                                                    min="{{ $bookingMinDate }}"
                                                                    max="{{ $bookingMaxDate }}"
                                                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 p-4 text-sm font-bold text-slate-900 focus:ring-4 ring-blue-500/10 transition-all duration-300"
                                                                    required
                                                                >
                                                            </div>
                                                            <div class="space-y-2">
                                                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ $catalogQuickEndDateLabel }}</label>
                                                                <input
                                                                    type="date"
                                                                    name="rental_end_date"
                                                                    x-model="quickEnd"
                                                                    :min="quickStart || minDate"
                                                                    :max="maxDate"
                                                                    class="w-full rounded-2xl border-slate-200 bg-slate-50/50 p-4 text-sm font-bold text-slate-900 focus:ring-4 ring-blue-500/10 transition-all duration-300"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>

                                                        <div class="space-y-2">
                                                            <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ $catalogQuickQtyLabel }}</label>
                                                            <div class="relative flex items-center">
                                                                <button type="button" @click="quickQty = Math.max(1, quickQty - 1)" class="absolute left-2 h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all active:scale-90">-</button>
                                                                <input
                                                                    type="number"
                                                                    name="qty"
                                                                    min="1"
                                                                    :max="maxQty"
                                                                    x-model="quickQty"
                                                                    class="no-spinner w-full rounded-2xl border-slate-200 bg-slate-50/50 py-4 text-center text-sm font-black text-slate-900 focus:ring-4 ring-blue-500/10 transition-all"
                                                                    required
                                                                >
                                                                <button type="button" @click="quickQty = Math.min(maxQty, quickQty + 1)" class="absolute right-2 h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all active:scale-90">+</button>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-2 gap-4 rounded-3xl bg-blue-600 p-6 text-white shadow-xl shadow-blue-500/20 overflow-hidden relative">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50"></div>
                                                            <div class="relative">
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-white/70">{{ $catalogQuickDurationLabel }}</p>
                                                                <p class="mt-1 text-lg font-black" x-text="calcDays() > 0 ? `${calcDays()} {{ $dayLabel }}` : '-'"></p>
                                                            </div>
                                                            <div class="relative text-right border-l border-white/20 pl-4">
                                                                <p class="text-[10px] font-black uppercase tracking-widest text-white/70">{{ $catalogQuickEstimateLabel }}</p>
                                                                <p class="mt-1 text-lg font-black" x-text="calcTotal() > 0 ? `{{ $currencyPrefix }} ${formatIdr(calcTotal())}` : '{{ $currencyPrefix }} -'"></p>
                                                            </div>
                                                        </div>

                                                        <div class="flex gap-4 pt-2">
                                                            <button type="button" class="btn-secondary flex-1 py-4 rounded-2xl font-bold tracking-wide" @click="quickOpen = false">
                                                                {{ $catalogQuickCancelButton }}
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                class="btn-primary flex-1 py-4 rounded-2xl font-bold tracking-wide shadow-blue-600/30 disabled:opacity-50 disabled:translate-y-0"
                                                                :disabled="!quickStart || !quickEnd || calcDays() <= 0"
                                                            >
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
                    </div>
                @empty
                    <div class="glass-lg noise-overlay rounded-[3rem] p-16 text-center shadow-2xl animate-fade-up">
                        <div class="mx-auto w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center mb-6 border border-slate-100">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ $emptyTitle }}</h3>
                        <p class="mt-4 text-base font-medium text-slate-500 max-w-sm mx-auto leading-relaxed">{{ $emptySubtitle }}</p>
                        <a href="{{ route('catalog') }}" class="mt-8 btn-primary inline-flex px-8 py-4 rounded-2xl font-black tracking-widest uppercase text-xs">
                            Refresh Katalog
                        </a>
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

@extends('layouts.landing')

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
        
        /* Subtle Entrance Animations */
        .catalog-enter {
            animation: catalog-enter 520ms ease-out both;
        }

        .catalog-stagger {
            animation: catalog-card-in 520ms ease-out both;
        }

        @keyframes catalog-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes catalog-card-in {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .catalog-enter,
            .catalog-stagger {
                animation: none !important;
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
        $catalogFallbackImage = site_asset('MANAKE-FAV-M.png');
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
        $resolvePublicStatusLabel = static function (string $statusValue, int $availableUnits): string {
            $normalized = strtolower(trim($statusValue));

            return match ($normalized) {
                'maintenance' => 'Maintenance',
                'unavailable' => 'Tidak Tersedia',
                'ready' => $availableUnits > 0 ? 'Tersedia' : 'Penuh / Sedang Disewa',
                default => $availableUnits > 0 ? 'Tersedia' : 'Tidak Tersedia',
            };
        };
        $resolvePublicStatusClass = static function (string $statusValue, int $availableUnits): string {
            $normalized = strtolower(trim($statusValue));

            return match ($normalized) {
                'maintenance' => 'border-amber-400/35 bg-amber-950/80 text-amber-200',
                'unavailable' => 'border-rose-400/35 bg-rose-950/80 text-rose-200',
                'ready' => $availableUnits > 0
                    ? 'border-emerald-400/35 bg-emerald-950/80 text-emerald-200'
                    : 'border-amber-400/35 bg-amber-950/80 text-amber-200',
                default => $availableUnits > 0
                    ? 'border-emerald-400/35 bg-emerald-950/80 text-emerald-200'
                    : 'border-rose-400/35 bg-rose-950/80 text-rose-200',
            };
        };
    @endphp

    <div
        x-data="catalogIdleHamburger({
            enabled: false,
            delay: {{ (int) $idleHamburgerDelayMs }},
            step: {{ (int) $idleHamburgerStepMs }},
            total: {{ (int) $categories->count() }},
        })"
        x-init="init()"
        @mouseenter="markPointerEnter"
        @mousemove.passive="markPointerMove"
        @mouseleave="markPointerLeave"
        @touchstart.passive="stopGuide"
        class="min-h-screen bg-[#0A0A0B] text-[#E8E8EC]"
    >
        <section class="relative overflow-hidden pt-6 pb-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
                <!-- Clean Header Card -->
                <div class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8 lg:p-10 shadow-2xl catalog-enter">
                    <div class="space-y-4 max-w-3xl">
                        <div>
                            <p class="section-kicker font-bold tracking-widest uppercase text-[#D4A843]/80">{{ __('ui.nav.catalog') }}</p>
                            <h1 class="text-2xl font-extrabold tracking-tight text-[#E8E8EC] sm:text-4xl leading-tight mt-1">
                                {{ $catalogTitle }}
                            </h1>
                            <p class="text-sm text-[#A0A0A8] leading-relaxed mt-2">
                                {{ $catalogSubtitle }}
                            </p>
                        </div>
                    </div>

                    <!-- Balanced Search Bar -->
                    <div class="max-w-3xl mt-6">
                        <form action="{{ route('catalog') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#66666C]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text" name="q" value="{{ $search }}" placeholder="Cari kamera, lighting, drone, audio..." 
                                       aria-label="Cari kamera, lighting, drone, audio..."
                                       autocomplete="off"
                                       class="w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] pl-12 pr-4 py-3.5 text-sm text-[#E8E8EC] placeholder:text-[#66666C] focus:border-[#D4A843] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/20">
                            </div>
                            <button type="submit" class="rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-bold text-[#0A0A0B] transition hover:bg-[#e0ba5d] shrink-0 sm:w-auto w-full">
                                <span>Cari Alat</span>
                            </button>
                        </form>
                    </div>

                    <!-- Clean Category Filter Layout -->
                    <div class="mt-5 border-t border-[#1A1A1E] pt-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-[#A0A0A8]">{{ $categoryLabel }}</p>
                            @if ($search !== '')
                                <a href="{{ route('catalog', $activeCategorySlug !== '' ? ['category' => $activeCategorySlug] : []) }}" class="inline-flex rounded-xl border border-[#1A1A1E] bg-[#111113] px-4 py-2 text-xs font-bold text-[#E8E8EC] transition hover:border-[#D4A843]/30 hover:text-[#D4A843]">
                                    {{ $catalogResetSearchLabel }}
                                </a>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2.5 pb-1">
                            <a
                                href="{{ route('catalog', $search !== '' ? ['q' => $search] : []) }}"
                                class="rounded-full border px-4 py-1.5 text-xs font-bold tracking-tight transition-all duration-300 shrink-0 {{ $activeCategorySlug === '' ? 'bg-[#D4A843] text-[#0A0A0B] border-[#D4A843]' : 'bg-[#111113] text-[#A0A0A8] border-[#1A1A1E] hover:border-[#D4A843]/40 hover:text-[#E8E8EC]' }}"
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
                                    class="relative rounded-full border px-4 py-1.5 text-xs font-bold tracking-tight transition-all duration-300 shrink-0 {{ $activeCategorySlug === $category->slug ? 'bg-[#D4A843] text-[#0A0A0B] border-[#D4A843]' : 'bg-[#111113] text-[#A0A0A8] border-[#1A1A1E] hover:border-[#D4A843]/40 hover:text-[#E8E8EC]' }}"
                                >
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($search !== '')
                    <div class="mt-6 inline-flex items-center gap-3 rounded-2xl border border-[#1A1A1E] bg-[#111113] px-4 py-3 catalog-enter">
                        <div class="h-2 w-2 rounded-full bg-[#D4A843] animate-pulse"></div>
                        <p class="text-sm font-medium text-[#A0A0A8]">
                            {{ $catalogSearchResultPrefix }} <span class="font-bold text-[#E8E8EC]">&quot;{{ $search }}&quot;</span>
                        </p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Product Grid Sections -->
        <section class="pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                @forelse ($groups as $group)
                    @php
                        $category = $group['category'];
                        $items = collect($group['items'] ?? []);
                    @endphp

                    <div class="mb-12 catalog-enter">
                        <div class="mb-6 flex flex-col gap-2 border-b border-[#1A1A1E] pb-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-[#E8E8EC]">{{ $category->name }}</h2>
                                @if (!empty($category->description))
                                    <p class="mt-1 max-w-2xl text-xs font-medium text-[#A0A0A8]">{{ $category->description }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#1A1A1E] bg-[#111113]/80 px-4 py-1 text-xs font-bold text-[#A0A0A8] shadow-sm self-start sm:self-auto">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#D4A843]"></span>
                                {{ $items->count() }} {{ $catalogItemSuffix }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $item)
                            @php
                                $statusValue = (string) ($item->status ?? ($item->stock > 0 ? 'ready' : 'unavailable'));
                                $imagePath = $item->image_path ?? $item->image;
                                $image = site_media_url($imagePath) ?: $catalogFallbackImage;
                                $prioritizeImage = ($loop->parent?->first ?? false) && $loop->index < 3;
                                $reservedUnits = (int) ($item->reserved_units ?? 0);
                                $availableUnits = (int) $item->available_units;
                                $canRent = $statusValue === 'ready' && (int) $item->stock > 0;
                                $statusLabel = $resolvePublicStatusLabel($statusValue, $availableUnits);
                                $statusClass = $resolvePublicStatusClass($statusValue, $availableUnits);
                            @endphp

                            <article
                                x-data="{
                                    quickOpen: false,
                                    quickQty: 1,
                                    quickStart: '',
                                    quickEnd: '',
                                    quickError: '',
                                    minDate: '{{ $bookingMinDate }}',
                                    maxDate: '{{ $bookingMaxDate }}',
                                    maxQty: {{ max((int) $item->stock, 1) }},
                                    parseDate(value) {
                                        if (!value) return null;
                                        const [year, month, day] = value.split('-').map(Number);
                                        if (!year || !month || !day) return null;
                                        return new Date(year, month - 1, day);
                                    },
                                    formatDate(date) {
                                        if (!date) return '';
                                        const y = date.getFullYear();
                                        const m = String(date.getMonth() + 1).padStart(2, '0');
                                        const d = String(date.getDate()).padStart(2, '0');
                                        return `${y}-${m}-${d}`;
                                    },
                                    addDays(dateString, days) {
                                        const d = this.parseDate(dateString);
                                        if (!d) return dateString;
                                        d.setDate(d.getDate() + days);
                                        const result = this.formatDate(d);
                                        const max = this.maxDate;
                                        return result > max ? max : result;
                                    },
                                    setPreset(durationDays, startOffset = 0) {
                                        const start = this.addDays(this.minDate, startOffset);
                                        const end = this.addDays(this.minDate, startOffset + durationDays - 1);
                                        this.quickStart = start;
                                        this.quickEnd = end > this.maxDate ? this.maxDate : end;
                                        this.quickError = '';
                                    },
                                    calcDays() {
                                        const start = this.parseDate(this.quickStart);
                                        const end = this.parseDate(this.quickEnd);
                                        if (!start || !end) return 0;
                                        const diff = Math.round((end - start) / 86400000) + 1;
                                        return diff > 0 ? diff : 0;
                                    },
                                    calcTotal() {
                                        const days = this.calcDays();
                                        const qty = Number(this.quickQty) || 1;
                                        return days > 0 ? days * {{ (int) $item->price_per_day }} * qty : 0;
                                    },
                                    formatIdr(value) {
                                        return new Intl.NumberFormat('{{ $intlLocale }}').format(value);
                                    },
                                    onStartChanged() {
                                        const start = this.parseDate(this.quickStart);
                                        const end = this.parseDate(this.quickEnd);
                                        if (!end || (start && end < start)) {
                                            this.quickEnd = this.quickStart;
                                        }
                                        this.quickError = '';
                                    },
                                    normalizeQty() {
                                        this.quickQty = Math.max(1, Math.min(this.maxQty, Number(this.quickQty) || 1));
                                    },
                                    canSubmit() {
                                        return this.quickStart && this.quickEnd && this.calcDays() > 0 && Number(this.quickQty) >= 1;
                                    },
                                    submitQuickOrder(event) {
                                        if (!this.canSubmit()) {
                                            event.preventDefault();
                                            this.quickError = 'Pilih tanggal sewa dan tanggal kembali terlebih dahulu.';
                                            return false;
                                        }
                                    },
                                    handleMouseMove(e) {
                                        const rect = $el.getBoundingClientRect();
                                        $el.style.setProperty('--x', (e.clientX - rect.left) + 'px');
                                        $el.style.setProperty('--y', (e.clientY - rect.top) + 'px');
                                    }
                                }"
                                @click="if (!$event.target.closest('button, a')) window.location.assign('{{ route('product.show', $item->slug) }}')"
                                class="group flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-[#1A1A1E] bg-[#111113] transition-all duration-300 hover:-translate-y-1 hover:border-[#D4A843]/30 hover:shadow-[0_24px_60px_-36px_rgba(212,168,67,0.45)] catalog-stagger"
                                style="animation-delay: {{ min($loop->index * 45, 240) }}ms"
                            >
                                <div class="relative aspect-[4/3] overflow-hidden bg-[#0A0A0B] p-5 flex items-center justify-center border-b border-[#1A1A1E]">
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $item->name }}"
                                        class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-[1.02] drop-shadow-md"
                                        onerror="this.onerror=null;this.src='{{ $catalogFallbackImage }}';"
                                        loading="{{ $prioritizeImage ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $prioritizeImage ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                    <div class="absolute inset-x-4 top-4 flex items-center justify-end pointer-events-none">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-widest {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col p-6">
                                    <h3 class="min-h-[2.5rem] text-lg font-bold leading-snug text-[#E8E8EC] transition-colors duration-300 group-hover:text-[#D4A843]">{{ $item->name }}</h3>
                                    
                                    <div class="mt-3 border-t border-[#1A1A1E] pt-3">
                                        <div class="flex items-baseline gap-1">
                                            <p class="text-xl font-extrabold tracking-tight text-[#E8E8EC]">
                                                {{ $currencyPrefix }} {{ number_format($item->price_per_day, 0, ',', '.') }}
                                            </p>
                                            <span class="text-[9px] font-bold uppercase tracking-widest text-[#A0A0A8]">{{ __('app.product.per_day') }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col gap-2">
                                        @auth
                                            @if ($canRent)
                                                <button
                                                    type="button"
                                                    class="mk-button-primary w-full py-3"
                                                    @click="quickOpen = true; quickQty = 1; quickStart = minDate; quickEnd = minDate; quickError = ''"
                                                >
                                                    {{ $catalogQuickOrderButton }}
                                                </button>
                                            @else
                                                <button type="button" disabled class="w-full rounded-md border border-[#1A1A1E] bg-[#0A0A0B] py-3 font-bold text-[#66666C] cursor-not-allowed">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endauth

                                        @guest
                                            @if ($canRent)
                                                <a
                                                    href="{{ route('login', ['reason' => 'cart']) }}"
                                                    @click.prevent="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: 'login' }))"
                                                    class="w-full rounded-md bg-[#D4A843] py-3 text-center font-bold text-[#0A0A0B] transition hover:bg-[#e0ba5d]"
                                                >
                                                    {{ $catalogLoginToOrderButton }}
                                                </a>
                                            @else
                                                <button type="button" disabled class="w-full rounded-md border border-[#1A1A1E] bg-[#0A0A0B] py-3 font-bold text-[#66666C] cursor-not-allowed">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endguest

                                        <a
                                            href="{{ route('product.show', $item->slug) }}"
                                            class="w-full rounded-md border border-[#1A1A1E] bg-[#111113] py-2.5 text-center font-bold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]"
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
                                                class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-[#0A0A0B]/80 backdrop-blur-md"
                                                @click.self="quickOpen = false"
                                                @keydown.escape.window="quickOpen = false"
                                                role="dialog"
                                                aria-modal="true"
                                                aria-label="Pesan Cepat {{ $item->name }}"
                                            >
                                                <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-[#1A1A1E] bg-[#111113] p-6 sm:p-8 max-h-[92dvh] overflow-y-auto">
                                                    <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-[#D4A843]/10 blur-[60px] pointer-events-none"></div>
                                                    
                                                    <!-- Header -->
                                                    <div class="relative flex items-start justify-between gap-4">
                                                        <div class="flex-1 min-w-0">
                                                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-1">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-[#D4A843] animate-pulse" aria-hidden="true"></span>
                                                                <span class="text-[10px] font-bold uppercase tracking-widest text-[#D4A843]">{{ $catalogQuickOrderTitle }}</span>
                                                            </div>
                                                            <h4 class="text-xl font-bold tracking-tight text-[#E8E8EC] leading-snug truncate">{{ $item->name }}</h4>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="shrink-0 h-9 w-9 flex items-center justify-center rounded-full border border-[#1A1A1E] bg-[#0A0A0B] text-[#A0A0A8] transition-all duration-300 hover:border-[#D4A843]/40 hover:text-[#E8E8EC] active:scale-90"
                                                            @click="quickOpen = false"
                                                            aria-label="Tutup pesan cepat"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </button>
                                                    </div>

                                                    <form
                                                        method="POST"
                                                        action="{{ route('cart.add') }}"
                                                        class="mt-6 space-y-5 relative"
                                                        @submit="submitQuickOrder($event)"
                                                    >
                                                        @csrf
                                                        <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                                        <input type="hidden" name="name" value="{{ $item->name }}">
                                                        <input type="hidden" name="slug" value="{{ $item->slug }}">
                                                        <input type="hidden" name="category" value="{{ $item->category?->name }}">
                                                        <input type="hidden" name="image" value="{{ $image }}">
                                                        <input type="hidden" name="price" value="{{ $item->price_per_day }}">

                                                        <!-- Preset Buttons -->
                                                        <div class="space-y-1.5">
                                                            <p class="text-[10px] font-bold uppercase tracking-widest text-[#66666C]">Pilih Cepat</p>
                                                            <div class="flex flex-wrap gap-2">
                                                                <button type="button"
                                                                    class="rounded-full border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-1.5 text-xs font-bold text-[#A0A0A8] transition hover:border-[#D4A843]/50 hover:text-[#D4A843] active:scale-95"
                                                                    @click="setPreset(1, 0)"
                                                                >Hari ini</button>
                                                                <button type="button"
                                                                    class="rounded-full border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-1.5 text-xs font-bold text-[#A0A0A8] transition hover:border-[#D4A843]/50 hover:text-[#D4A843] active:scale-95"
                                                                    @click="setPreset(1, 1)"
                                                                >Besok</button>
                                                                <button type="button"
                                                                    class="rounded-full border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-1.5 text-xs font-bold text-[#A0A0A8] transition hover:border-[#D4A843]/50 hover:text-[#D4A843] active:scale-95"
                                                                    @click="setPreset(3, 0)"
                                                                >3 Hari</button>
                                                                <button type="button"
                                                                    class="rounded-full border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-1.5 text-xs font-bold text-[#A0A0A8] transition hover:border-[#D4A843]/50 hover:text-[#D4A843] active:scale-95"
                                                                    @click="setPreset(7, 0)"
                                                                >7 Hari</button>
                                                            </div>
                                                        </div>

                                                        <!-- Date Inputs -->
                                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                            <div class="space-y-1.5">
                                                                <label for="quick-start-{{ $item->id }}" class="text-xs font-bold uppercase tracking-widest text-[#A0A0A8]">Tanggal Sewa</label>
                                                                <input
                                                                    id="quick-start-{{ $item->id }}"
                                                                    type="date"
                                                                    name="rental_start_date"
                                                                    x-model="quickStart"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    class="mk-input"
                                                                    @change="onStartChanged()"
                                                                    required
                                                                >
                                                            </div>
                                                            <div class="space-y-1.5">
                                                                <label for="quick-end-{{ $item->id }}" class="text-xs font-bold uppercase tracking-widest text-[#A0A0A8]">Tanggal Kembali</label>
                                                                <input
                                                                    id="quick-end-{{ $item->id }}"
                                                                    type="date"
                                                                    name="rental_end_date"
                                                                    x-model="quickEnd"
                                                                    :min="quickStart || minDate"
                                                                    :max="maxDate"
                                                                    class="mk-input"
                                                                    @change="quickError = ''"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>

                                                        <!-- Qty -->
                                                        <div class="space-y-1.5">
                                                            <label for="quick-qty-{{ $item->id }}" class="text-xs font-bold uppercase tracking-widest text-[#A0A0A8]">Jumlah Unit</label>
                                                            <div class="relative flex items-center">
                                                                <button
                                                                    type="button"
                                                                    @click="quickQty = Math.max(1, Number(quickQty) - 1)"
                                                                    class="absolute left-2 h-9 w-9 flex items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC] hover:border-[#D4A843]/40 transition-all active:scale-90 text-lg font-bold"
                                                                    aria-label="Kurangi jumlah"
                                                                >−</button>
                                                                <input
                                                                    id="quick-qty-{{ $item->id }}"
                                                                    type="number"
                                                                    name="qty"
                                                                    min="1"
                                                                    :max="maxQty"
                                                                    x-model.number="quickQty"
                                                                    class="mk-input no-spinner text-center"
                                                                    @change="normalizeQty()"
                                                                    required
                                                                >
                                                                <button
                                                                    type="button"
                                                                    @click="quickQty = Math.min(maxQty, Number(quickQty) + 1)"
                                                                    class="absolute right-2 h-9 w-9 flex items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] text-[#E8E8EC] hover:border-[#D4A843]/40 transition-all active:scale-90 text-lg font-bold"
                                                                    aria-label="Tambah jumlah"
                                                                >+</button>
                                                            </div>
                                                        </div>

                                                        <!-- Summary Panel -->
                                                        <div class="relative grid grid-cols-2 gap-4 overflow-hidden rounded-xl border border-[#1A1A1E] bg-[#D4A843] p-5 text-[#0A0A0B]">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50 pointer-events-none"></div>
                                                            <div class="relative">
                                                                <p class="text-[10px] font-bold uppercase tracking-widest text-[#0A0A0B]/60">Durasi</p>
                                                                <p class="mt-1 text-lg font-extrabold" x-text="calcDays() > 0 ? `${calcDays()} {{ $dayLabel }}` : '-'"></p>
                                                            </div>
                                                            <div class="relative text-right border-l border-black/20 pl-4">
                                                                <p class="text-[10px] font-bold uppercase tracking-widest text-[#0A0A0B]/60">Estimasi</p>
                                                                <p class="mt-1 text-base font-extrabold leading-tight" x-text="calcTotal() > 0 ? `{{ $currencyPrefix }} ${formatIdr(calcTotal())}` : '-'"></p>
                                                            </div>
                                                        </div>

                                                        <!-- Error message -->
                                                        <p
                                                            x-show="quickError"
                                                            x-text="quickError"
                                                            class="rounded-lg border border-rose-400/30 bg-rose-950/60 px-4 py-2 text-xs font-medium text-rose-300"
                                                            role="alert"
                                                        ></p>

                                                        <!-- Helper hint when not ready -->
                                                        <p
                                                            x-show="!canSubmit() && !quickError"
                                                            class="text-xs text-[#66666C] text-center"
                                                        >Pilih tanggal untuk mengaktifkan tombol Tambah.</p>

                                                        <!-- Action Buttons -->
                                                        <div class="flex gap-3 pt-1">
                                                            <button
                                                                type="button"
                                                                class="mk-button-secondary flex-1"
                                                                @click="quickOpen = false"
                                                            >
                                                                {{ $catalogQuickCancelButton }}
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                class="flex-1 rounded-xl px-5 py-3 text-sm font-bold transition-all duration-200 active:scale-95"
                                                                :class="canSubmit()
                                                                    ? 'bg-[#D4A843] text-[#0A0A0B] hover:bg-[#e0ba5d] shadow-[0_8px_24px_-8px_rgba(212,168,67,0.5)]'
                                                                    : 'bg-[#1A1A1E] text-[#66666C] cursor-not-allowed opacity-60'"
                                                                :disabled="!canSubmit()"
                                                            >
                                                                {{ $catalogQuickAddButton }}
                                                            </button>
                                                        </div>

                                                        <p class="text-center text-[10px] text-[#66666C]">Estimasi dihitung otomatis berdasarkan durasi dan jumlah unit.</p>
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
                    <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-16 text-center animate-fade-up">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border border-[#1A1A1E] bg-[#0A0A0B]">
                            <svg class="w-10 h-10 text-[#66666C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <h3 class="text-2xl font-bold tracking-tight text-[#E8E8EC]">{{ $emptyTitle }}</h3>
                        <p class="mx-auto mt-4 max-w-sm text-base font-medium leading-relaxed text-[#A0A0A8]">{{ $emptySubtitle }}</p>
                        <a href="{{ route('catalog') }}" class="mt-8 inline-flex rounded-md bg-[#D4A843] px-8 py-4 text-sm font-black text-[#0A0A0B]">
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

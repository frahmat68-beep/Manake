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

        /* Scoped Dynamic Accent Styling */
        .catalog-page {
            --catalog-accent: #D4A843;
            --catalog-accent-hover: #E0BA5D;
            --catalog-accent-text: #0A0A0B;
            --catalog-accent-soft: rgba(212, 168, 67, 0.14);
            --catalog-accent-border: rgba(212, 168, 67, 0.34);
            --catalog-accent-shadow: rgba(212, 168, 67, 0.45);

            --catalog-bg: #0A0A0B;
            --catalog-surface: #111113;
            --catalog-surface-soft: rgba(17, 17, 19, 0.70);
            --catalog-surface-muted: #0A0A0B;
            --catalog-border: #1A1A1E;
            --catalog-text: #E8E8EC;
            --catalog-muted: #A0A0A8;
            --catalog-placeholder: #66666C;
        }

        html[data-theme-resolved="light"] .catalog-page {
            --catalog-accent: #2563EB;
            --catalog-accent-hover: #1D4ED8;
            --catalog-accent-text: #FFFFFF;
            --catalog-accent-soft: rgba(37, 99, 235, 0.10);
            --catalog-accent-border: rgba(37, 99, 235, 0.24);
            --catalog-accent-shadow: rgba(37, 99, 235, 0.25);

            --catalog-bg: #F8FAFC;
            --catalog-surface: #FFFFFF;
            --catalog-surface-soft: rgba(255, 255, 255, 0.92);
            --catalog-surface-muted: #F8FAFC;
            --catalog-border: #E5E7EB;
            --catalog-text: #111827;
            --catalog-muted: #4B5563;
            --catalog-placeholder: #6B7280;
        }

        .catalog-page-bg {
            background-color: var(--catalog-bg) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-surface {
            background: var(--catalog-surface) !important;
            border-color: var(--catalog-border) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-surface-soft {
            background: var(--catalog-surface-soft) !important;
            border-color: var(--catalog-border) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-surface-muted {
            background: var(--catalog-surface-muted) !important;
            border-color: var(--catalog-border) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-title {
            color: var(--catalog-text) !important;
        }

        .catalog-muted {
            color: var(--catalog-muted) !important;
        }

        .catalog-border {
            border-color: var(--catalog-border) !important;
        }

        .catalog-input {
            background: var(--catalog-surface) !important;
            border: 1px solid var(--catalog-border) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-input::placeholder {
            color: var(--catalog-placeholder) !important;
        }

        .catalog-input:focus {
            border-color: var(--catalog-accent) !important;
            box-shadow: 0 0 0 2px var(--catalog-accent-soft) !important;
            outline: none !important;
        }

        .catalog-secondary-button {
            background: var(--catalog-surface) !important;
            border: 1px solid var(--catalog-border) !important;
            color: var(--catalog-text) !important;
        }

        .catalog-secondary-button:hover {
            border-color: var(--catalog-accent-border) !important;
            color: var(--catalog-accent) !important;
        }

        .catalog-accent-bg {
            background: var(--catalog-accent) !important;
            background-color: var(--catalog-accent) !important;
            color: var(--catalog-accent-text) !important;
            border-color: var(--catalog-accent) !important;
        }

        .catalog-accent-bg:hover {
            background: var(--catalog-accent-hover) !important;
            background-color: var(--catalog-accent-hover) !important;
        }

        html[data-theme-resolved="light"] .catalog-page .catalog-surface,
        html[data-theme-resolved="light"] .catalog-page .catalog-surface-soft {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
        }

        .catalog-accent-text {
            color: var(--catalog-accent) !important;
        }

        .catalog-accent-border-hover:hover {
            border-color: var(--catalog-accent-border) !important;
            color: var(--catalog-accent) !important;
        }

        .catalog-accent-dot {
            background-color: var(--catalog-accent) !important;
        }

        .catalog-accent-focus:focus {
            border-color: var(--catalog-accent) !important;
            box-shadow: 0 0 0 2px var(--catalog-accent-soft) !important;
        }

        .catalog-card-hover:hover {
            border-color: var(--catalog-accent-border) !important;
            box-shadow: 0 24px 60px -36px var(--catalog-accent-shadow) !important;
        }

        .group:hover .catalog-card-title-hover {
            color: var(--catalog-accent) !important;
        }

        .catalog-suggestion-link:hover,
        .catalog-suggestion-link:focus {
            border-color: var(--catalog-accent-border) !important;
        }
        .catalog-suggestion-link:hover .catalog-suggestion-name {
            color: var(--catalog-accent) !important;
        }
        .catalog-suggestion-badge {
            background-color: var(--catalog-accent-soft) !important;
            color: var(--catalog-accent) !important;
        }
        .catalog-suggestion-category {
            color: var(--catalog-accent) !important;
        }

        .catalog-quick-modal-root {
            background: rgba(10, 10, 11, 0.72);
            backdrop-filter: blur(10px);
        }

        html[data-theme-resolved="light"] .catalog-quick-modal-root {
            background: rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(8px);
        }

        .catalog-quick-modal-panel {
            background: var(--catalog-surface);
            border-color: var(--catalog-border);
            color: var(--catalog-text);
            box-shadow: 0 30px 90px -45px rgba(0, 0, 0, 0.55);
        }

        .catalog-quick-title {
            color: var(--catalog-text);
        }

        .catalog-quick-muted {
            color: var(--catalog-muted);
        }

        .catalog-quick-soft {
            background: var(--catalog-surface-muted);
            border-color: var(--catalog-border);
            color: var(--catalog-text);
        }

        html[data-theme-resolved="light"] .catalog-quick-soft {
            background: #F8FAFC;
        }

        .catalog-quick-input {
            width: 100%;
            border-radius: 0.875rem;
            border: 1px solid var(--catalog-border);
            background: var(--catalog-surface);
            color: var(--catalog-text);
            min-height: 3.5rem;
            outline: none;
            transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease, color 160ms ease;
        }

        .catalog-quick-input:focus {
            border-color: var(--catalog-accent);
            box-shadow: 0 0 0 2px var(--catalog-accent-soft);
        }

        html[data-theme-resolved="light"] .catalog-quick-input,
        html[data-theme-resolved="light"] .catalog-page .catalog-quick-input {
            background: #FFFFFF !important;
            background-color: #FFFFFF !important;
            color: #111827 !important;
            border-color: #DADDE3 !important;
            color-scheme: light !important;
        }

        html[data-theme-resolved="light"] .catalog-quick-input::placeholder,
        html[data-theme-resolved="light"] .catalog-page .catalog-quick-input::placeholder {
            color: #6B7280 !important;
        }

        html[data-theme-resolved="light"] .catalog-quick-input:focus,
        html[data-theme-resolved="light"] .catalog-page .catalog-quick-input:focus {
            border-color: #2563EB !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.14) !important;
        }

        html[data-theme-resolved="dark"] .catalog-quick-input {
            color-scheme: dark;
        }

        .catalog-quick-control {
            background: var(--catalog-surface-muted);
            border-color: var(--catalog-border);
            color: var(--catalog-text);
        }

        html[data-theme-resolved="light"] .catalog-quick-control,
        html[data-theme-resolved="light"] .catalog-page .catalog-quick-control {
            background: #F3F4F6 !important;
            background-color: #F3F4F6 !important;
            border-color: #DADDE3 !important;
            color: #111827 !important;
        }

        html[data-theme-resolved="light"] .catalog-quick-control:hover,
        html[data-theme-resolved="light"] .catalog-page .catalog-quick-control:hover {
            border-color: rgba(37, 99, 235, 0.24) !important;
            color: #2563EB !important;
        }

        html[data-theme-resolved="light"] .catalog-quick-input:-webkit-autofill,
        html[data-theme-resolved="light"] .catalog-quick-input:-webkit-autofill:hover,
        html[data-theme-resolved="light"] .catalog-quick-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #111827 !important;
            box-shadow: 0 0 0 1000px #FFFFFF inset !important;
            caret-color: #111827 !important;
            transition: background-color 9999s ease-in-out 0s !important;
        }

        html[data-theme-resolved="light"] input[type="date"].catalog-quick-input,
        html[data-theme-resolved="light"] input[type="number"].catalog-quick-input {
            background-color: #FFFFFF !important;
            color: #111827 !important;
            color-scheme: light !important;
        }

        .catalog-quick-secondary {
            background: transparent;
            border: 1px solid #1A1A1E;
            color: #E8E8EC;
        }

        html[data-theme-resolved="light"] .catalog-quick-secondary {
            border-color: #DADDE3;
            color: #111827;
            background: #FFFFFF;
        }

        .catalog-quick-secondary:hover {
            border-color: var(--catalog-accent-border);
            color: var(--catalog-accent);
        }

        .catalog-quick-primary {
            background: var(--catalog-accent) !important;
            color: var(--catalog-accent-text) !important;
            border: 1px solid var(--catalog-accent) !important;
        }

        .catalog-quick-primary:hover {
            background: var(--catalog-accent-hover) !important;
        }

        .catalog-quick-primary-disabled {
            background: #1A1A1E;
            color: #66666C;
            cursor: not-allowed;
            opacity: 0.6;
        }

        html[data-theme-resolved="light"] .catalog-quick-primary-disabled {
            background: #E5E7EB;
            color: #9CA3AF;
        }

        .catalog-quick-summary {
            background: var(--catalog-accent) !important;
            color: var(--catalog-accent-text) !important;
            border-color: var(--catalog-accent) !important;
        }

        .catalog-quick-summary-divider {
            border-color: rgba(0, 0, 0, 0.20);
        }

        html[data-theme-resolved="light"] .catalog-quick-summary-divider {
            border-color: rgba(255, 255, 255, 0.35);
        }

        html[data-theme-resolved="light"] .catalog-quick-error {
            background: #FFF1F2 !important;
            color: #BE123C !important;
            border-color: rgba(244, 63, 94, 0.30) !important;
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
        $dayLabelSingular = __('app.product.day_label_singular');
        $dayLabelPlural = __('app.product.day_label_plural');
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
        $catalogQuickDateRequiredMessage = __('ui.catalog.quick_date_required_message');
        $catalogQuickPickLabel = __('ui.catalog.quick_pick_label');
        $catalogQuickPresetToday = __('ui.catalog.quick_preset_today');
        $catalogQuickPresetTomorrow = __('ui.catalog.quick_preset_tomorrow');
        $catalogQuickPreset3Days = __('ui.catalog.quick_preset_3_days');
        $catalogQuickPreset7Days = __('ui.catalog.quick_preset_7_days');
        $catalogQuickSelectDatesHint = __('ui.catalog.quick_select_dates_hint');
        $catalogQuickEstimateNote = __('ui.catalog.quick_estimate_note');
        $catalogQuickDecreaseQtyAria = __('ui.catalog.quick_decrease_qty_aria');
        $catalogQuickIncreaseQtyAria = __('ui.catalog.quick_increase_qty_aria');
        $bookingMinDate = now()->toDateString();
        $bookingMaxDate = now()->addMonthsNoOverflow(3)->toDateString();
        $idleHamburgerEnabledRaw = strtolower(trim((string) setting('catalog.idle_hamburger_enabled', '1')));
        $idleHamburgerEnabled = ! in_array($idleHamburgerEnabledRaw, ['0', 'false', 'off', 'no', 'tidak'], true);
        $idleHamburgerDelayMs = max(1000, min(12000, (int) setting('catalog.idle_hamburger_delay_ms', 2200)));
        $idleHamburgerStepMs = max(500, min(4000, (int) setting('catalog.idle_hamburger_step_ms', 900)));
        
        $resolveCategoryDisplayName = static function ($category): string {
            $rawName = trim((string) ($category->name ?? ''));
            $slug = strtolower(trim((string) ($category->slug ?? '')));
            $locale = app()->getLocale();

            if ($locale !== 'en') {
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

        $resolvePublicStatusLabel = static function (string $statusValue, int $availableUnits): string {
            $normalized = strtolower(trim($statusValue));

            return match ($normalized) {
                'maintenance' => __('ui.catalog.status_maintenance'),
                'unavailable' => __('ui.catalog.status_unavailable'),
                'ready' => $availableUnits > 0
                    ? __('ui.catalog.status_available')
                    : __('ui.catalog.status_fully_booked'),
                default => $availableUnits > 0
                    ? __('ui.catalog.status_available')
                    : __('ui.catalog.status_unavailable'),
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
        class="catalog-page catalog-page-bg min-h-screen"
    >
        <section class="relative overflow-hidden pt-6 pb-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10">
                <!-- Clean Header Card -->
                <div class="catalog-surface-soft rounded-3xl border p-6 sm:p-8 lg:p-10 shadow-2xl catalog-enter">
                    <div class="space-y-4 max-w-3xl">
                        <div>
                            <p class="section-kicker font-bold tracking-widest uppercase catalog-accent-text">{{ __('ui.nav.catalog') }}</p>
                            <h1 class="catalog-title text-2xl font-extrabold tracking-tight sm:text-4xl leading-tight mt-1">
                                {{ $catalogTitle }}
                            </h1>
                            <p class="catalog-muted text-sm leading-relaxed mt-2">
                                {{ $catalogSubtitle }}
                            </p>
                        </div>
                    </div>

                    <!-- Balanced Search Bar -->
                    <div class="max-w-3xl mt-6">
                        <form action="{{ route('catalog') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 catalog-muted">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text" name="q" id="catalog-search-input" value="{{ $search }}" placeholder="{{ __('ui.catalog.search_placeholder') }}" 
                                       aria-label="{{ __('ui.catalog.search_aria_label') }}"
                                       autocomplete="off"
                                       class="catalog-input w-full rounded-xl pl-12 pr-12 py-3.5 text-sm">
                                <div id="search-loading-spinner" class="absolute right-4 top-1/2 -translate-y-1/2 hidden">
                                    <svg class="animate-spin h-5 w-5 catalog-accent-text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <div id="search-suggestions-dropdown" class="catalog-surface absolute left-0 right-0 top-[calc(100%+0.5rem)] z-[60] hidden rounded-2xl border backdrop-blur-xl p-2 shadow-2xl space-y-1 max-h-80 overflow-y-auto"></div>
                            </div>
                            <button type="submit" class="rounded-xl catalog-accent-bg px-6 py-3.5 text-sm font-bold transition shrink-0 sm:w-auto w-full">
                                <span>{{ __('ui.catalog.search_button') }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- Clean Category Filter Layout -->
                    <div class="mt-5 border-t catalog-border pt-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="catalog-muted text-[10px] font-bold uppercase tracking-widest">{{ $categoryLabel }}</p>
                            @if ($search !== '')
                                <a href="{{ route('catalog', $activeCategorySlug !== '' ? ['category' => $activeCategorySlug] : []) }}" class="catalog-secondary-button inline-flex rounded-xl px-4 py-2 text-xs font-bold transition">
                                    {{ $catalogResetSearchLabel }}
                                </a>
                            @endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2.5 pb-1">
                            <a
                                href="{{ route('catalog', $search !== '' ? ['q' => $search] : []) }}"
                                class="rounded-full border px-4 py-1.5 text-xs font-bold tracking-tight transition-all duration-300 shrink-0 {{ $activeCategorySlug === '' ? 'catalog-accent-bg' : 'catalog-secondary-button catalog-accent-border-hover' }}"
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
                                    class="relative rounded-full border px-4 py-1.5 text-xs font-bold tracking-tight transition-all duration-300 shrink-0 {{ $activeCategorySlug === $category->slug ? 'catalog-accent-bg' : 'catalog-secondary-button catalog-accent-border-hover' }}"
                                >
                                    {{ $resolveCategoryDisplayName($category) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($search !== '')
                    <div class="catalog-surface mt-6 inline-flex items-center gap-3 rounded-2xl border px-4 py-3 catalog-enter">
                        <div class="h-2 w-2 rounded-full catalog-accent-dot animate-pulse"></div>
                        <p class="catalog-muted text-sm font-medium">
                            {{ $catalogSearchResultPrefix }} <span class="catalog-title font-bold">&quot;{{ $search }}&quot;</span>
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
                        <div class="mb-6 flex flex-col gap-2 border-b catalog-border pb-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="catalog-title text-2xl font-bold tracking-tight">{{ $resolveCategoryDisplayName($category) }}</h2>
                                @if (!empty($category->description))
                                    <p class="catalog-muted mt-1 max-w-2xl text-xs font-medium">{{ $category->description }}</p>
                                @endif
                            </div>
                            <span class="catalog-surface inline-flex items-center gap-2 rounded-full border px-4 py-1 text-xs font-bold shadow-sm self-start sm:self-auto">
                                <span class="h-1.5 w-1.5 rounded-full catalog-accent-dot"></span>
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
                                    quickQty: '1',
                                    quickStart: '',
                                    quickEnd: '',
                                    quickError: '',
                                    locale: '{{ app()->getLocale() }}',
                                    minDate: '{{ $bookingMinDate }}',
                                    maxDate: '{{ $bookingMaxDate }}',
                                    maxQty: {{ max((int) $item->stock, 1) }},
                                    parseDate(value) {
                                        if (!value) return null;
                                        const [year, month, day] = String(value).split('-').map(Number);
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
                                        const date = this.parseDate(dateString);
                                        if (!date) return this.minDate;
                                        date.setDate(date.getDate() + days);
                                        return this.formatDate(date);
                                    },
                                    clampDate(dateString) {
                                        const date = this.parseDate(dateString);
                                        const min = this.parseDate(this.minDate);
                                        const max = this.parseDate(this.maxDate);
                                        if (!date) return this.minDate;
                                        if (min && date < min) return this.minDate;
                                        if (max && date > max) return this.maxDate;
                                        return this.formatDate(date);
                                    },
                                    setDateRange(start, end) {
                                        this.quickStart = this.clampDate(start);
                                        this.quickEnd = this.clampDate(end);
                                        const s = this.parseDate(this.quickStart);
                                        const e = this.parseDate(this.quickEnd);
                                        if (s && e && e < s) this.quickEnd = this.quickStart;
                                        this.quickError = '';
                                    },
                                    setPreset(days, offset = 0) {
                                        const start = this.addDays(this.minDate, offset);
                                        const end = this.addDays(start, Math.max(days - 1, 0));
                                        this.setDateRange(start, end);
                                    },
                                    onStartChanged() {
                                        this.quickStart = this.clampDate(this.quickStart);
                                        const start = this.parseDate(this.quickStart);
                                        const end = this.parseDate(this.quickEnd);
                                        if (!end || (start && end < start)) {
                                            this.quickEnd = this.quickStart;
                                        }
                                        this.quickError = '';
                                    },
                                    onEndChanged() {
                                        this.quickEnd = this.clampDate(this.quickEnd);
                                        const start = this.parseDate(this.quickStart);
                                        const end = this.parseDate(this.quickEnd);
                                        if (start && end && end < start) {
                                            this.quickEnd = this.quickStart;
                                        }
                                        this.quickError = '';
                                    },
                                    normalizeQty() {
                                        const parsed = parseInt(this.quickQty, 10);
                                        this.quickQty = String(Math.max(1, Math.min(this.maxQty, Number.isFinite(parsed) ? parsed : 1)));
                                    },
                                    openDatePicker(event) {
                                        const input = event?.target;
                                        if (!(input instanceof HTMLInputElement)) return;
                                        if (typeof input.showPicker === 'function') {
                                            try {
                                                input.showPicker();
                                            } catch (error) {
                                                input.focus();
                                            }
                                        } else {
                                            input.focus();
                                        }
                                    },
                                    decreaseQty() {
                                        this.quickQty = String(Math.max(1, (parseInt(this.quickQty, 10) || 1) - 1));
                                        this.normalizeQty();
                                    },
                                    increaseQty() {
                                        this.quickQty = String(Math.min(this.maxQty, (parseInt(this.quickQty, 10) || 1) + 1));
                                        this.normalizeQty();
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
                                        const qty = parseInt(this.quickQty, 10) || 1;
                                        return days > 0 ? days * {{ (int) $item->price_per_day }} * qty : 0;
                                    },
                                    formatIdr(value) {
                                        return new Intl.NumberFormat('{{ $intlLocale }}').format(value);
                                    },
                                    canSubmit() {
                                        return this.quickStart && this.quickEnd && this.calcDays() > 0 && (parseInt(this.quickQty, 10) || 0) >= 1;
                                    },
                                    submitQuickOrder(event) {
                                        this.normalizeQty();
                                        if (!this.canSubmit()) {
                                            event.preventDefault();
                                            this.quickError = @js($catalogQuickDateRequiredMessage);
                                            return false;
                                        }
                                        this.quickError = '';
                                    }
                                }"
                                @click="if (!$event.target.closest('button, a')) window.location.assign('{{ route('product.show', $item->slug) }}')"
                                class="catalog-surface group flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border transition-all duration-300 hover:-translate-y-1 catalog-card-hover catalog-stagger"
                                style="animation-delay: {{ min($loop->index * 45, 240) }}ms"
                            >
                                <div class="catalog-surface-muted relative aspect-[4/3] overflow-hidden p-5 flex items-center justify-center border-b">
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
                                    <h3 class="catalog-title min-h-[2.5rem] text-lg font-bold leading-snug transition-colors duration-300 catalog-card-title-hover">{{ $item->name }}</h3>
                                    
                                    <div class="mt-3 border-t catalog-border pt-3">
                                        <div class="flex items-baseline gap-1">
                                            <p class="catalog-title text-xl font-extrabold tracking-tight">
                                                {{ $currencyPrefix }} {{ number_format($item->price_per_day, 0, ',', '.') }}
                                            </p>
                                            <span class="catalog-muted text-[9px] font-bold uppercase tracking-widest">{{ __('app.product.per_day') }}</span>
                                        </div>
                                    </div>
 
                                    <div class="mt-6 flex flex-col gap-2">
                                        @auth
                                            @if ($canRent)
                                                <button
                                                    type="button"
                                                    class="catalog-accent-bg w-full rounded-md py-3 text-center font-bold transition"
                                                    @click="quickOpen = true; quickQty = '1'; quickStart = minDate; quickEnd = minDate; quickError = ''"
                                                >
                                                    {{ $catalogQuickOrderButton }}
                                                </button>
                                            @else
                                                <button type="button" disabled class="catalog-surface-muted catalog-muted w-full rounded-md border py-3 font-bold cursor-not-allowed opacity-70">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endauth
 
                                        @guest
                                            @if ($canRent)
                                                <a
                                                    href="{{ route('login', ['reason' => 'cart']) }}"
                                                    class="w-full rounded-md catalog-accent-bg py-3 text-center font-bold transition"
                                                >
                                                    {{ $catalogLoginToOrderButton }}
                                                </a>
                                            @else
                                                <button type="button" disabled class="catalog-surface-muted catalog-muted w-full rounded-md border py-3 font-bold cursor-not-allowed opacity-70">
                                                    {{ $catalogOutOfStockButton }}
                                                </button>
                                            @endif
                                        @endguest
 
                                        <a
                                            href="{{ route('product.show', $item->slug) }}"
                                            class="catalog-secondary-button catalog-accent-border-hover w-full rounded-md py-2.5 text-center font-bold transition"
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
                                                class="catalog-page catalog-quick-modal-root fixed inset-0 z-[100] flex items-end justify-center p-3 sm:items-center sm:p-4"
                                                @click.self="quickOpen = false"
                                                @keydown.escape.window="quickOpen = false"
                                                role="dialog"
                                                aria-modal="true"
                                                aria-label="{{ __('ui.catalog.quick_order_dialog_aria', ['name' => $item->name]) }}"
                                            >
                                                <div
                                                    class="catalog-quick-modal-panel relative w-full max-w-lg rounded-2xl border p-6 sm:p-8 max-h-[92dvh] overflow-y-auto"
                                                    @click.stop
                                                >
                                                    <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-current opacity-10 blur-[60px] pointer-events-none catalog-accent-text"></div>
                                                    
                                                    <!-- Header -->
                                                    <div class="relative flex items-start justify-between gap-4">
                                                        <div class="flex-1 min-w-0">
                                                            <div class="catalog-quick-soft mb-2 inline-flex items-center gap-2 rounded-full border px-3 py-1">
                                                                <span class="h-1.5 w-1.5 rounded-full catalog-accent-dot animate-pulse" aria-hidden="true"></span>
                                                                <span class="text-[10px] font-bold uppercase tracking-widest catalog-accent-text">{{ $catalogQuickOrderTitle }}</span>
                                                            </div>
                                                            <h4 class="catalog-quick-title text-xl font-bold tracking-tight leading-snug truncate">{{ $item->name }}</h4>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="catalog-quick-soft catalog-accent-border-hover shrink-0 h-9 w-9 flex items-center justify-center rounded-full border transition-all duration-300 active:scale-90"
                                                            @click.stop="quickOpen = false"
                                                            aria-label="{{ __('ui.catalog.quick_close_aria') }}"
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
                                                            <p class="catalog-quick-muted text-[10px] font-bold uppercase tracking-widest">{{ $catalogQuickPickLabel }}</p>
                                                            <div class="flex flex-wrap gap-2">
                                                                <button type="button"
                                                                    class="catalog-quick-soft catalog-accent-border-hover rounded-full border px-3 py-1.5 text-xs font-bold transition active:scale-95"
                                                                    @click.stop="setPreset(1, 0)"
                                                                >{{ $catalogQuickPresetToday }}</button>
                                                                <button type="button"
                                                                    class="catalog-quick-soft catalog-accent-border-hover rounded-full border px-3 py-1.5 text-xs font-bold transition active:scale-95"
                                                                    @click.stop="setPreset(1, 1)"
                                                                >{{ $catalogQuickPresetTomorrow }}</button>
                                                                <button type="button"
                                                                    class="catalog-quick-soft catalog-accent-border-hover rounded-full border px-3 py-1.5 text-xs font-bold transition active:scale-95"
                                                                    @click.stop="setPreset(3, 0)"
                                                                >{{ $catalogQuickPreset3Days }}</button>
                                                                <button type="button"
                                                                    class="catalog-quick-soft catalog-accent-border-hover rounded-full border px-3 py-1.5 text-xs font-bold transition active:scale-95"
                                                                    @click.stop="setPreset(7, 0)"
                                                                >{{ $catalogQuickPreset7Days }}</button>
                                                            </div>
                                                        </div>

                                                        <!-- Date Inputs -->
                                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                            <div class="space-y-1.5">
                                                                <label for="quick-start-{{ $item->id }}" class="catalog-quick-muted text-xs font-bold uppercase tracking-widest">{{ $catalogQuickStartDateLabel }}</label>
                                                                <input
                                                                    id="quick-start-{{ $item->id }}"
                                                                    type="date"
                                                                    name="rental_start_date"
                                                                    x-model="quickStart"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    class="catalog-quick-input cursor-pointer px-4"
                                                                    @click.stop
                                                                    @focus="openDatePicker($event)"
                                                                    @change="onStartChanged()"
                                                                    @input="onStartChanged()"
                                                                    required
                                                                >
                                                            </div>
                                                            <div class="space-y-1.5">
                                                                <label for="quick-end-{{ $item->id }}" class="catalog-quick-muted text-xs font-bold uppercase tracking-widest">{{ $catalogQuickEndDateLabel }}</label>
                                                                <input
                                                                    id="quick-end-{{ $item->id }}"
                                                                    type="date"
                                                                    name="rental_end_date"
                                                                    x-model="quickEnd"
                                                                    :min="quickStart || minDate"
                                                                    :max="maxDate"
                                                                    class="catalog-quick-input cursor-pointer px-4"
                                                                    @click.stop
                                                                    @focus="openDatePicker($event)"
                                                                    @change="onEndChanged()"
                                                                    @input="onEndChanged()"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>

                                                        <!-- Qty -->
                                                        <div class="space-y-1.5">
                                                            <label for="quick-qty-{{ $item->id }}" class="catalog-quick-muted text-xs font-bold uppercase tracking-widest">{{ $catalogQuickQtyLabel }}</label>
                                                            <div class="relative flex items-center">
                                                                <button
                                                                    type="button"
                                                                    @click.stop="decreaseQty()"
                                                                    :disabled="quickQty <= 1"
                                                                    :class="quickQty <= 1
                                                                        ? 'opacity-40 cursor-not-allowed'
                                                                        : 'catalog-accent-border-hover active:scale-90'"
                                                                    class="catalog-quick-control absolute left-2 z-10 h-9 w-9 flex items-center justify-center rounded-xl border transition-all text-lg font-bold"
                                                                    aria-label="{{ $catalogQuickDecreaseQtyAria }}"
                                                                >−</button>
                                                                <input
                                                                    id="quick-qty-{{ $item->id }}"
                                                                    type="number"
                                                                    name="qty"
                                                                    min="1"
                                                                    :max="maxQty"
                                                                    x-model="quickQty"
                                                                    inputmode="numeric"
                                                                    pattern="[0-9]*"
                                                                    class="catalog-quick-input no-spinner px-14 text-center font-sans"
                                                                    @click.stop
                                                                    @input="normalizeQty()"
                                                                    @change="normalizeQty()"
                                                                    required
                                                                >
                                                                <button
                                                                    type="button"
                                                                    @click.stop="increaseQty()"
                                                                    :disabled="quickQty >= maxQty"
                                                                    :class="quickQty >= maxQty
                                                                        ? 'opacity-40 cursor-not-allowed'
                                                                        : 'catalog-accent-border-hover active:scale-90'"
                                                                    class="catalog-quick-control absolute right-2 z-10 h-9 w-9 flex items-center justify-center rounded-xl border transition-all text-lg font-bold"
                                                                    aria-label="{{ $catalogQuickIncreaseQtyAria }}"
                                                                >+</button>
                                                            </div>
                                                            <!-- Stock helper text -->
                                                            <p class="catalog-quick-muted text-[11px] ml-1"
                                                               x-text="locale === 'en' ? `Max ${maxQty} units available.` : `Maks. ${maxQty} unit tersedia.`"
                                                            ></p>
                                                        </div>

                                                        <!-- Summary Panel -->
                                                        <div class="catalog-quick-summary relative grid grid-cols-2 gap-4 overflow-hidden rounded-xl border p-5">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-50 pointer-events-none"></div>
                                                            <div class="relative">
                                                                <p class="text-[10px] font-bold uppercase tracking-widest text-current opacity-60">{{ $catalogQuickDurationLabel }}</p>
                                                                <p class="mt-1 text-lg font-extrabold" x-text="calcDays() > 0 ? `${calcDays()} ${calcDays() === 1 ? @js($dayLabelSingular) : @js($dayLabelPlural)}` : '-'"></p>
                                                            </div>
                                                            <div class="catalog-quick-summary-divider relative text-right border-l pl-4">
                                                                <p class="text-[10px] font-bold uppercase tracking-widest text-current opacity-60">{{ $catalogQuickEstimateLabel }}</p>
                                                                <p class="mt-1 text-base font-extrabold leading-tight" x-text="calcTotal() > 0 ? `{{ $currencyPrefix }} ${formatIdr(calcTotal())}` : '-'"></p>
                                                            </div>
                                                        </div>

                                                        <!-- Error message -->
                                                        <p
                                                            x-show="quickError"
                                                            x-text="quickError"
                                                            class="catalog-quick-error rounded-lg border border-rose-400/30 bg-rose-950/60 px-4 py-2 text-xs font-medium text-rose-300"
                                                            role="alert"
                                                        ></p>

                                                        <!-- Helper hint when not ready -->
                                                        <p
                                                            x-show="!canSubmit() && !quickError"
                                                            class="catalog-quick-muted text-xs text-center"
                                                        >{{ $catalogQuickSelectDatesHint }}</p>

                                                        <!-- Action Buttons -->
                                                        <div class="flex gap-3 pt-1">
                                                            <button
                                                                type="button"
                                                                class="catalog-quick-secondary flex-1 rounded-xl px-5 py-3 text-sm font-bold transition"
                                                                @click.stop="quickOpen = false"
                                                            >
                                                                {{ $catalogQuickCancelButton }}
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                class="flex-1 rounded-xl px-5 py-3 text-sm font-bold transition-all duration-200"
                                                                :class="canSubmit()
                                                                    ? 'catalog-quick-primary active:scale-95'
                                                                    : 'catalog-quick-primary-disabled'"
                                                                :disabled="!canSubmit()"
                                                            >
                                                                {{ $catalogQuickAddButton }}
                                                            </button>
                                                        </div>

                                                        <p class="catalog-quick-muted text-center text-[10px]">{{ $catalogQuickEstimateNote }}</p>
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
                        <a href="{{ route('catalog') }}" class="mt-8 inline-flex rounded-md catalog-accent-bg px-8 py-4 text-sm font-black transition">
                            {{ app()->getLocale() === 'en' ? 'Refresh Catalog' : 'Refresh Katalog' }}
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

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('catalog-search-input');
            const spinner = document.getElementById('search-loading-spinner');
            const dropdown = document.getElementById('search-suggestions-dropdown');

            if (!searchInput || !dropdown) return;

            let debounceTimeout = null;
            let currentFocusIndex = -1;
            let suggestionsData = [];

            const showDropdown = () => {
                dropdown.classList.remove('hidden');
            };

            const hideDropdown = () => {
                dropdown.classList.add('hidden');
                currentFocusIndex = -1;
            };

            const renderSuggestions = (items) => {
                suggestionsData = items;
                const noSuggestionsLabel = @js(app()->getLocale() === 'en' ? 'No suggestions' : 'Tidak ada saran');
                if (!items || items.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-3 text-center text-xs text-[#A0A0A8]">
                            ${noSuggestionsLabel}
                        </div>
                    `;
                    showDropdown();
                    return;
                }

                const locale = '{{ $intlLocale }}';
                const currency = '{{ $currencyPrefix }}';
                const recommendationLabel = @js(app()->getLocale() === 'en' ? 'Recommended' : 'Rekomendasi');
                const perDayLabel = @js(app()->getLocale() === 'en' ? '/day' : '/hari');
                const availableLabel = @js(app()->getLocale() === 'en' ? 'Available' : 'Tersedia');
                const unitLabel = @js(app()->getLocale() === 'en' ? 'units' : 'unit');
                
                dropdown.innerHTML = items.map((item, index) => {
                    const priceFormatted = new Intl.NumberFormat(locale).format(item.price_per_day);
                    return `
                        <a href="${item.detail_url}" 
                           data-suggestion-index="${index}"
                           class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-white/5 group border border-transparent active:scale-[0.99] focus:outline-none focus:bg-white/5 catalog-suggestion-link">
                            <div class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-[#0A0A0B] p-1 flex items-center justify-center border border-[#1A1A1E]">
                                <img src="${item.image_url}" alt="${item.name}" class="h-full w-full object-contain">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="truncate text-xs font-bold text-[#E8E8EC] catalog-suggestion-name transition-colors">${item.name}</span>
                                    ${item.is_recommended ? `<span class="rounded px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider catalog-suggestion-badge">${recommendationLabel}</span>` : ''}
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[10px] text-[#A0A0A8] flex-wrap">
                                    <span class="catalog-suggestion-category font-semibold">${item.category_name}</span>
                                    <span class="h-1 w-1 rounded-full bg-white/10"></span>
                                    <span>${currency} ${priceFormatted}${perDayLabel}</span>
                                    <span class="h-1 w-1 rounded-full bg-white/10"></span>
                                    <span>${availableLabel}: ${item.available_units} ${unitLabel}</span>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');
                showDropdown();
            };

            const fetchSuggestions = async (query) => {
                if (spinner) spinner.classList.remove('hidden');
                try {
                    const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}`);
                    if (response.ok) {
                        const json = await response.json();
                        renderSuggestions(json.data || []);
                    }
                } catch (err) {
                    console.error('Error fetching suggestions:', err);
                } finally {
                    if (spinner) spinner.classList.add('hidden');
                }
            };

            searchInput.addEventListener('input', () => {
                const val = searchInput.value.trim();
                
                if (debounceTimeout) {
                    clearTimeout(debounceTimeout);
                }

                if (val.length < 2) {
                    hideDropdown();
                    return;
                }

                debounceTimeout = setTimeout(() => {
                    fetchSuggestions(val);
                }, 300);
            });

            const updateFocus = () => {
                const items = dropdown.querySelectorAll('a[data-suggestion-index]');
                items.forEach((item, index) => {
                    if (index === currentFocusIndex) {
                        item.classList.add('bg-white/5', 'border-[#D4A843]/20');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('bg-white/5', 'border-[#D4A843]/20');
                    }
                });
            };

            searchInput.addEventListener('keydown', (e) => {
                const items = dropdown.querySelectorAll('a[data-suggestion-index]');
                if (dropdown.classList.contains('hidden')) {
                    if (e.key === 'ArrowDown' && searchInput.value.trim().length >= 2) {
                        fetchSuggestions(searchInput.value.trim());
                    }
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length > 0) {
                        currentFocusIndex = (currentFocusIndex + 1) % items.length;
                        updateFocus();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length > 0) {
                        currentFocusIndex = (currentFocusIndex - 1 + items.length) % items.length;
                        updateFocus();
                    }
                } else if (e.key === 'Enter') {
                    if (currentFocusIndex >= 0 && suggestionsData[currentFocusIndex]) {
                        e.preventDefault();
                        window.location.href = suggestionsData[currentFocusIndex].detail_url;
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    hideDropdown();
                    searchInput.focus();
                }
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#catalog-search-input') && !e.target.closest('#search-suggestions-dropdown')) {
                    hideDropdown();
                }
            });

            searchInput.addEventListener('focus', () => {
                const val = searchInput.value.trim();
                if (val.length >= 2) {
                    if (dropdown.children.length > 0) {
                        showDropdown();
                    } else {
                        fetchSuggestions(val);
                    }
                }
            });
        });
    </script>
@endpush

<x-app-layout>
    @section('title', __('ui.cart_page.page_title'))

    @php
        $cartCopy = __('ui.cart_page');
        $cartItemCount = count($cartItems);
        $cartUnitCount = collect($cartItems)->sum(fn (array $item) => (int) ($item['qty'] ?? 1));
        $hasCartItems = $cartItemCount > 0;
        $rentalDays = 0;

        if ($cartSuggestedStartDate && $cartSuggestedEndDate) {
            $rentalDays = \Carbon\Carbon::parse($cartSuggestedStartDate)->diffInDays(\Carbon\Carbon::parse($cartSuggestedEndDate)) + 1;
        }
        $dayLabel = $rentalDays === 1 ? $cartCopy['day_singular'] : $cartCopy['day_plural'];

        $resolveCartCategoryName = static function (?string $name) use ($cartCopy): string {
            $rawName = trim((string) $name);
            if (! app()->isLocale('en')) {
                return $rawName ?: $cartCopy['uncategorized'];
            }
            return match (strtolower($rawName)) {
                'aksesoris', 'aksesori' => 'Accessories',
                'kamera' => 'Camera',
                'lensa' => 'Lens',
                'lampu' => 'Lighting',
                default => $rawName ?: $cartCopy['uncategorized'],
            };
        };
    @endphp

    @push('head')
        <style>
            .cart-enter {
                animation: cart-enter 520ms ease-out both;
            }

            .cart-card-in {
                animation: cart-card-in 520ms ease-out both;
            }

            @keyframes cart-enter {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes cart-card-in {
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
                .cart-enter,
                .cart-card-in {
                    animation: none !important;
                }
            }

            .cart-page {
                --cart-accent: #D4A843;
                --cart-accent-hover: #E0BA5D;
                --cart-accent-text: #0A0A0B;
                --cart-accent-soft: rgba(212, 168, 67, 0.12);
                --cart-accent-border: rgba(212, 168, 67, 0.28);
                --cart-bg: #0A0A0B;
                --cart-surface: #111113;
                --cart-surface-soft: rgba(17, 17, 19, 0.70);
                --cart-surface-muted: #0A0A0B;
                --cart-border: #1A1A1E;
                --cart-text: #E8E8EC;
                --cart-muted: #A0A0A8;
            }

            html[data-theme-resolved="light"] .cart-page {
                --cart-accent: #2563EB;
                --cart-accent-hover: #1D4ED8;
                --cart-accent-text: #FFFFFF;
                --cart-accent-soft: rgba(37, 99, 235, 0.10);
                --cart-accent-border: rgba(37, 99, 235, 0.24);
                --cart-bg: #F8FAFC;
                --cart-surface: #FFFFFF;
                --cart-surface-soft: rgba(255, 255, 255, 0.92);
                --cart-surface-muted: #F8FAFC;
                --cart-border: #E5E7EB;
                --cart-text: #111827;
                --cart-muted: #4B5563;
            }

            .cart-page-bg {
                background-color: var(--cart-bg) !important;
                color: var(--cart-text) !important;
            }

            .cart-card {
                background: var(--cart-surface-soft) !important;
                border-color: var(--cart-border) !important;
                color: var(--cart-text) !important;
            }

            .cart-card-solid {
                background: var(--cart-surface) !important;
                border-color: var(--cart-border) !important;
                color: var(--cart-text) !important;
            }

            .cart-inner {
                background: var(--cart-surface-muted) !important;
                border-color: var(--cart-border) !important;
                color: var(--cart-text) !important;
            }

            .cart-title {
                color: var(--cart-text) !important;
            }

            .cart-muted {
                color: var(--cart-muted) !important;
            }

            .cart-border {
                border-color: var(--cart-border) !important;
            }

            .cart-accent-text {
                color: var(--cart-accent) !important;
            }

            .cart-accent-bg {
                background: var(--cart-accent) !important;
                background-color: var(--cart-accent) !important;
                color: var(--cart-accent-text) !important;
                border-color: var(--cart-accent) !important;
            }

            .cart-accent-bg:hover {
                background: var(--cart-accent-hover) !important;
                background-color: var(--cart-accent-hover) !important;
            }

            .cart-accent-soft {
                background: var(--cart-accent-soft) !important;
                border-color: var(--cart-accent-border) !important;
                color: var(--cart-accent) !important;
            }

            .cart-accent-dot {
                background-color: var(--cart-accent) !important;
            }

            .cart-secondary-button {
                background: var(--cart-surface) !important;
                border: 1px solid var(--cart-border) !important;
                color: var(--cart-text) !important;
            }

            .cart-secondary-button:hover {
                border-color: var(--cart-accent-border) !important;
                color: var(--cart-accent) !important;
            }

            .cart-accent-link:hover {
                color: var(--cart-accent) !important;
            }

            html[data-theme-resolved="light"] .cart-page .cart-card,
            html[data-theme-resolved="light"] .cart-page .cart-card-solid {
                box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
            }

            .cart-alert-success {
                border-color: rgba(16, 185, 129, 0.28) !important;
                background: rgba(6, 78, 59, 0.38) !important;
                color: #A7F3D0 !important;
            }

            .cart-alert-error {
                border-color: rgba(244, 63, 94, 0.28) !important;
                background: rgba(136, 19, 55, 0.38) !important;
                color: #FDA4AF !important;
            }

            html[data-theme-resolved="light"] .cart-alert-success {
                background: #ECFDF5 !important;
                color: #047857 !important;
            }

            html[data-theme-resolved="light"] .cart-alert-error {
                background: #FFF1F2 !important;
                color: #BE123C !important;
            }

            .cart-carousel-shell {
                position: relative;
            }

            .cart-carousel-track {
                display: grid;
                grid-auto-flow: column;
                grid-auto-columns: minmax(260px, 1fr);
                gap: 1rem;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-snap-type: x mandatory;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
                padding: 0.25rem 0.125rem 1rem;
                scrollbar-width: none;
            }

            .cart-carousel-track::-webkit-scrollbar {
                display: none;
            }

            @media (min-width: 640px) {
                .cart-carousel-track {
                    grid-auto-columns: minmax(280px, 0.5fr);
                }
            }

            @media (min-width: 1024px) {
                .cart-carousel-track {
                    grid-auto-columns: minmax(290px, 0.25fr);
                }
            }

            .cart-carousel-item {
                scroll-snap-align: start;
                min-width: 0;
            }

            .cart-carousel-control {
                background: var(--cart-surface) !important;
                border: 1px solid var(--cart-border) !important;
                color: var(--cart-text) !important;
                box-shadow: 0 18px 40px -28px rgba(0, 0, 0, 0.35);
            }

            .cart-carousel-control:hover {
                border-color: var(--cart-accent-border) !important;
                color: var(--cart-accent) !important;
                transform: translateY(-1px);
            }

            .cart-carousel-control:disabled {
                cursor: not-allowed;
                opacity: 0.42;
                transform: none;
            }

            .cart-carousel-fade-left,
            .cart-carousel-fade-right {
                pointer-events: none;
                position: absolute;
                top: 0;
                bottom: 0;
                z-index: 5;
                width: 3rem;
            }

            .cart-carousel-fade-left {
                left: 0;
                background: linear-gradient(90deg, var(--cart-bg), transparent);
            }

            .cart-carousel-fade-right {
                right: 0;
                background: linear-gradient(270deg, var(--cart-bg), transparent);
            }

            @media (max-width: 639px) {
                .cart-carousel-fade-left,
                .cart-carousel-fade-right {
                    display: none;
                }
            }

            .cart-carousel-card {
                height: 100%;
                transition:
                    transform 180ms ease,
                    border-color 180ms ease,
                    box-shadow 180ms ease,
                    background-color 180ms ease;
            }

            .cart-carousel-card:hover {
                transform: translateY(-3px);
                border-color: var(--cart-accent-border) !important;
            }

            html[data-theme-resolved="light"] .cart-carousel-card:hover {
                box-shadow: 0 24px 55px -38px rgba(37, 99, 235, 0.35);
            }

            html[data-theme-resolved="dark"] .cart-carousel-card:hover {
                box-shadow: 0 24px 55px -38px rgba(212, 168, 67, 0.30);
            }

            @media (prefers-reduced-motion: reduce) {
                .cart-carousel-track {
                    scroll-behavior: auto;
                }

                .cart-carousel-card,
                .cart-carousel-control {
                    transition: none !important;
                }
            }
        </style>
    @endpush

    <div class="cart-page cart-page-bg min-h-screen">
        <div class="mx-auto max-w-7xl px-4 py-8 pb-24 sm:px-6 sm:py-10 lg:px-8">
            <header class="cart-card cart-enter rounded-3xl border p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.30)] sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <h1 class="cart-title text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ $cartCopy['title'] }}
                        </h1>
                        <p class="cart-muted max-w-2xl text-sm leading-6 sm:text-base">
                            {{ $cartCopy['subtitle'] }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:justify-end">
                        <div class="cart-card-solid inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold">
                            <span class="cart-accent-dot h-2 w-2 rounded-full"></span>
                            {{ trans_choice('ui.cart_page.item_count', $cartUnitCount, ['count' => number_format($cartUnitCount, 0, ',', '.')]) }}
                        </div>

                        @if ($hasCartItems)
                            <form
                                action="{{ route('cart.clear') }}"
                                method="POST"
                                class="inline-flex"
                                onsubmit="return confirm('{{ $cartCopy['clear_confirm'] }}');"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-400/25 bg-rose-500/5 px-4 py-2.5 text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-400/30"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ $cartCopy['clear_all'] }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </header>

            @if (session('success') || session('error'))
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.75fr)] lg:gap-8">
                    <div>
                        @if (session('success'))
                            <div class="cart-alert-success rounded-2xl border px-4 py-3 text-sm font-medium cart-card-in">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="cart-alert-error rounded-2xl border px-4 py-3 text-sm font-medium cart-card-in">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-rose-300"></span>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if (! $hasCartItems)
                <section class="mt-8">
                    <article class="cart-card cart-card-in rounded-3xl border px-6 py-14 text-center sm:px-10 sm:py-16">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl border cart-inner">
                            <svg class="cart-muted h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h2 class="cart-title mt-6 text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ $cartCopy['empty_title'] }}
                        </h2>
                        <p class="cart-muted mx-auto mt-3 max-w-md text-sm leading-6 sm:text-base">
                            {{ $cartCopy['empty_subtitle'] }}
                        </p>
                        <a
                            href="{{ route('catalog') }}"
                            class="cart-accent-bg mt-8 inline-flex items-center justify-center rounded-xl px-6 py-3.5 text-sm font-semibold transition"
                        >
                            {{ $cartCopy['view_catalog'] }}
                        </a>
                    </article>
                </section>
            @else
                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.75fr)] lg:items-start">
                    <section class="space-y-5">
                        @foreach ($cartItems as $item)
                            @php
                                $itemPrice = (int) ($item['price'] ?? 0);
                                $itemQty = (int) ($item['qty'] ?? 1);
                                $itemStock = max((int) ($item['stock'] ?? 99), 1);
                                $days = 1;

                                if (!empty($item['rental_start_date']) && !empty($item['rental_end_date'])) {
                                    $days = \Carbon\Carbon::parse($item['rental_start_date'])->diffInDays(\Carbon\Carbon::parse($item['rental_end_date'])) + 1;
                                }

                                $lineSubtotal = $itemPrice * $itemQty * $days;
                                $imageUrl = $item['image_url'] ?? site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment');
                                $dateRangeLabel = !empty($item['rental_start_date']) && !empty($item['rental_end_date'])
                                    ? \Carbon\Carbon::parse($item['rental_start_date'])->translatedFormat('d M Y') . ' — ' . \Carbon\Carbon::parse($item['rental_end_date'])->translatedFormat('d M Y')
                                    : $cartCopy['date_not_selected'];
                            @endphp

                            <article
                                class="cart-card cart-card-in rounded-3xl border p-5 shadow-[0_26px_70px_-52px_rgba(0,0,0,0.30)] sm:p-6"
                                style="animation-delay: {{ min($loop->index * 55, 220) }}ms"
                            >
                                <div class="flex flex-col gap-5 xl:flex-row xl:items-center">
                                    <div class="cart-inner aspect-[4/3] w-full overflow-hidden rounded-2xl border xl:w-[140px] xl:shrink-0">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item['name'] }}"
                                            class="h-full w-full object-contain p-4"
                                            onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                        >
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-4">
                                        <div class="space-y-2">
                                            <span class="cart-accent-soft inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold">
                                                {{ $resolveCartCategoryName($item['category'] ?? null) }}
                                            </span>
                                            <h3 class="cart-title text-xl font-bold leading-tight">
                                                <a href="{{ route('product.show', $item['slug'] ?? '#') }}" class="cart-accent-link transition">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-sm text-[#A0A0A8]">
                                            <div class="cart-card-solid inline-flex items-center gap-2 rounded-full border px-3 py-1.5">
                                                <svg class="cart-accent-text h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="cart-muted">{{ $dateRangeLabel }}</span>
                                            </div>
                                            <div class="cart-card-solid cart-title inline-flex items-center rounded-full border px-3 py-1.5">
                                                @php $itemDayLabel = $days === 1 ? $cartCopy['day_singular'] : $cartCopy['day_plural']; @endphp
                                                {{ $days }} {{ $itemDayLabel }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full xl:w-[260px] xl:shrink-0">
                                        <div class="cart-inner rounded-2xl border p-4">
                                            <div class="space-y-1">
                                                <p class="cart-title text-2xl font-bold tracking-tight">
                                                    Rp {{ number_format($lineSubtotal, 0, ',', '.') }}
                                                </p>
                                                <p class="cart-muted text-xs leading-5">
                                                    {{ strtr($cartCopy['line_formula'], [
                                                        ':price' => number_format($itemPrice, 0, ',', '.'),
                                                        ':qty' => $itemQty,
                                                        ':days' => $days,
                                                        ':day_label' => $itemDayLabel,
                                                    ]) }}
                                                </p>
                                            </div>

                                            <div class="mt-4 space-y-3">
                                                <div>
                                                    <p class="cart-muted mb-2 text-xs font-semibold">{{ $cartCopy['quantity_label'] }}</p>
                                                    <div class="flex items-center gap-2">
                                                        <form method="POST" action="{{ route('cart.decrement', $item['key']) }}" class="shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="submit"
                                                                class="cart-secondary-button flex h-11 w-11 items-center justify-center rounded-xl transition focus:outline-none focus:ring-2 focus:ring-[var(--cart-accent-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                                                aria-label="{{ $cartCopy['decrease_qty_aria'] }}"
                                                                @disabled($itemQty <= 1)
                                                            >
                                                                <span class="text-lg font-bold">−</span>
                                                            </button>
                                                        </form>

                                                        <div class="cart-inner flex h-11 min-w-[72px] items-center justify-center rounded-xl border px-3 text-base font-semibold">
                                                            {{ $itemQty }}
                                                        </div>

                                                        <form method="POST" action="{{ route('cart.increment', $item['key']) }}" class="shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="submit"
                                                                class="cart-secondary-button flex h-11 w-11 items-center justify-center rounded-xl transition focus:outline-none focus:ring-2 focus:ring-[var(--cart-accent-soft)] disabled:cursor-not-allowed disabled:opacity-40"
                                                                aria-label="{{ $cartCopy['increase_qty_aria'] }}"
                                                                @disabled($itemQty >= $itemStock)
                                                            >
                                                                <span class="text-lg font-bold">+</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <p class="cart-muted mt-2 text-xs">
                                                        {{ strtr($cartCopy['stock_available'], [':stock' => number_format($itemStock, 0, ',', '.')]) }}
                                                    </p>
                                                </div>

                                                <form
                                                    method="POST"
                                                    action="{{ route('cart.remove', $item['key']) }}"
                                                    onsubmit="return confirm('{{ $cartCopy['delete_item_confirm'] }}');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-400/25 bg-rose-500/5 px-4 py-2.5 text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-400/30"
                                                        aria-label="{{ $cartCopy['delete_item_aria'] }}"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        {{ $cartCopy['delete_item'] }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <section class="cart-card-in lg:sticky lg:top-28" style="animation-delay: 120ms">
                        <article class="cart-card rounded-3xl border p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.30)] sm:p-7">
                            <div class="space-y-1">
                                <h2 class="cart-title text-2xl font-bold tracking-tight">
                                    {{ $cartCopy['summary_title'] }}
                                </h2>
                                <p class="cart-muted text-sm">
                                    {{ $cartCopy['summary_subtitle'] }}
                                </p>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="cart-inner rounded-2xl border p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="cart-muted text-xs font-semibold">{{ $cartCopy['rental_period'] }}</p>
                                            @if ($cartSuggestedStartDate && $cartSuggestedEndDate)
                                                <p class="cart-title mt-1 text-sm font-semibold leading-6">
                                                    {{ \Carbon\Carbon::parse($cartSuggestedStartDate)->translatedFormat('d M Y') }}
                                                    —
                                                    {{ \Carbon\Carbon::parse($cartSuggestedEndDate)->translatedFormat('d M Y') }}
                                                </p>
                                            @else
                                                <p class="cart-title mt-1 text-sm font-semibold leading-6">
                                                    {{ $cartCopy['follow_selected_dates'] }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="cart-accent-soft inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold">
                                            @if ($rentalDays > 0)
                                                {{ $rentalDays }} {{ $rentalDays === 1 ? $cartCopy['day_singular'] : $cartCopy['day_plural'] }}
                                            @else
                                                {{ $cartCopy['duration_auto'] }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="cart-inner rounded-2xl border p-4">
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="cart-muted">{{ $cartCopy['subtotal'] }}</span>
                                            <span class="cart-title font-semibold">Rp {{ number_format($estimatedSubtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="cart-muted">{{ $cartCopy['tax'] }}</span>
                                            <span class="cart-title font-semibold">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="cart-border border-t pt-3">
                                            <div class="flex items-end justify-between gap-4">
                                                <span class="cart-title text-sm font-semibold">{{ $cartCopy['total'] }}</span>
                                                <span class="cart-accent-text text-2xl font-bold tracking-tight">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('checkout') }}"
                                    class="cart-accent-bg inline-flex w-full items-center justify-center rounded-xl px-5 py-3.5 text-sm font-semibold transition"
                                >
                                    {{ $cartCopy['checkout'] }}
                                </a>
                            </div>
                        </article>
                    </section>
                </div>
            @endif

            @if ($hasCartItems && $suggestedEquipments->isNotEmpty())
                <aside
                    class="mt-12"
                    x-data="{
                        canPrev: false,
                        canNext: false,
                        init() {
                            this.$nextTick(() => this.update());
                        },
                        update() {
                            const el = this.$refs.track;
                            if (!el) return;
                            this.canPrev = el.scrollLeft > 8;
                            this.canNext = el.scrollLeft + el.clientWidth < el.scrollWidth - 8;
                        },
                        scroll(direction) {
                            const el = this.$refs.track;
                            if (!el) return;
                            const amount = Math.max(el.clientWidth * 0.82, 280);
                            el.scrollBy({
                                left: direction === 'next' ? amount : -amount,
                                behavior: 'smooth'
                            });
                            window.setTimeout(() => this.update(), 260);
                        }
                    }"
                    x-init="init()"
                >
                    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div class="space-y-1">
                            <h2 class="cart-title text-2xl font-bold tracking-tight">
                                {{ $cartCopy['suggestions_title'] }}
                            </h2>
                            <p class="cart-muted text-sm">
                                {{ $cartCopy['suggestions_subtitle'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="cart-carousel-control flex h-10 w-10 items-center justify-center rounded-full transition"
                                @click="scroll('prev')"
                                :disabled="!canPrev"
                                aria-label="{{ app()->isLocale('en') ? 'Previous recommendation' : 'Rekomendasi sebelumnya' }}"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="cart-carousel-control flex h-10 w-10 items-center justify-center rounded-full transition"
                                @click="scroll('next')"
                                :disabled="!canNext"
                                aria-label="{{ app()->isLocale('en') ? 'Next recommendation' : 'Rekomendasi berikutnya' }}"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="cart-carousel-shell">
                        <div class="cart-carousel-fade-left" x-show="canPrev" x-transition.opacity></div>
                        <div class="cart-carousel-fade-right" x-show="canNext" x-transition.opacity></div>

                        <div
                            x-ref="track"
                            class="cart-carousel-track"
                            @scroll.debounce.80ms="update()"
                            @keydown.arrow-left.prevent="scroll('prev')"
                            @keydown.arrow-right.prevent="scroll('next')"
                            tabindex="0"
                            role="region"
                            aria-label="{{ app()->isLocale('en') ? 'Recommended equipment carousel' : 'Carousel rekomendasi alat' }}"
                        >
                            @foreach ($suggestedEquipments as $suggestion)
                                <article class="cart-carousel-item">
                                    <div class="cart-card cart-carousel-card rounded-3xl border p-4">
                                        <div class="cart-inner aspect-[4/3] overflow-hidden rounded-2xl border">
                                            <img
                                                src="{{ site_media_url($suggestion->image_path ?? $suggestion->image ?? null) ?: config('placeholders.equipment') }}"
                                                alt="{{ $suggestion->name }}"
                                                class="h-full w-full object-contain p-3"
                                                onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                            >
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <span class="cart-accent-soft inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold">
                                                {{ $resolveCartCategoryName($suggestion->category->name ?? null) }}
                                            </span>
                                            <h3 class="line-clamp-2 text-base font-semibold leading-6 cart-title">
                                                <a href="{{ route('product.show', $suggestion->slug) }}" class="cart-accent-link transition">
                                                    {{ $suggestion->name }}
                                                </a>
                                            </h3>
                                            <div class="flex items-center justify-between gap-4">
                                                <p class="cart-title text-base font-semibold">
                                                    Rp {{ number_format($suggestion->price_per_day, 0, ',', '.') }}
                                                    <span class="cart-muted text-xs font-medium">/ {{ $rentalDays === 1 ? $cartCopy['day_singular'] : $cartCopy['day_plural'] }}</span>
                                                </p>
                                                <a
                                                    href="{{ route('product.show', $suggestion->slug) }}"
                                                    class="cart-secondary-button inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition"
                                                >
                                                    {{ $cartCopy['view_item'] }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
</x-app-layout>

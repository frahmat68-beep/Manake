@extends('layouts.admin', ['activePage' => 'dashboard'])

@section('title', __('ui.admin_dashboard.title'))
@section('page_title', __('ui.admin_dashboard.page_title'))

@push('head')
<style>
    .admin-dashboard-page {
        color: var(--admin-text);
    }

    /* Cards Visual Consistency */
    .admin-dashboard-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-dashboard-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-dashboard-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    /* Kicker label */
    .admin-dashboard-kicker {
        color: var(--admin-muted);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    /* Semantic kicker colours */
    .admin-kicker-sky   { color: #0284C7 !important; }
    .admin-kicker-amber { color: #B45309 !important; }
    .admin-kicker-emerald { color: #047857 !important; }
    .admin-kicker-rose  { color: #BE123C !important; }

    html[data-theme-resolved="dark"] .admin-kicker-sky   { color: #7DD3FC !important; }
    html[data-theme-resolved="dark"] .admin-kicker-amber { color: #FDE68A !important; }
    html[data-theme-resolved="dark"] .admin-kicker-emerald { color: #6EE7B7 !important; }
    html[data-theme-resolved="dark"] .admin-kicker-rose  { color: #FDA4AF !important; }

    /* Overview Grid */
    .admin-overview-grid {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .admin-overview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .admin-overview-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: stretch;
        }
    }

    /* Overview Cards */
    .admin-overview-card {
        min-height: 136px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .admin-overview-card-main {
        display: grid;
        gap: 0.35rem;
    }

    .admin-overview-value {
        color: var(--admin-text);
        font-size: clamp(1.8rem, 2.2vw, 2.35rem);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.045em;
    }

    .admin-overview-desc {
        color: var(--admin-muted);
        font-size: 0.74rem;
        line-height: 1.45;
    }

    html[data-theme-resolved="light"] .admin-overview-desc {
        color: #4B5563;
    }

    html[data-theme-resolved="dark"] .admin-overview-desc {
        color: #A0A0A8;
    }

    /* Finance Cards */
    .admin-finance-card {
        min-height: 138px;
    }

    .admin-finance-value {
        margin-top: 1rem;
        color: var(--admin-text);
        font-size: clamp(1.4rem, 2vw, 1.65rem);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    html[data-theme-resolved="light"] .admin-finance-value {
        color: #111827;
    }

    html[data-theme-resolved="dark"] .admin-finance-value {
        color: #E8E8EC;
    }

    .admin-finance-desc {
        margin-top: 0.55rem;
        color: var(--admin-muted);
        font-size: 0.78rem;
        line-height: 1.55;
    }

    /* Card Section Header */
    .admin-dashboard-card-header {
        border-color: var(--admin-border);
        background: var(--admin-surface-raised);
    }

    html[data-theme-resolved="light"] .admin-dashboard-card-header {
        background: #FFFFFF;
        border-color: #E5E7EB;
    }

    html[data-theme-resolved="dark"] .admin-dashboard-card-header {
        background: #151519;
        border-color: #1A1A1E;
    }

    /* Order Rows */
    .admin-order-row {
        border-color: var(--admin-border);
    }

    .admin-order-row + .admin-order-row {
        border-top: 1px solid var(--admin-border);
    }

    .admin-order-row:hover {
        background: var(--admin-surface-raised);
    }

    html[data-theme-resolved="light"] .admin-order-row:hover {
        background: #F8FAFC;
    }

    html[data-theme-resolved="dark"] .admin-order-row:hover {
        background: #151519;
    }

    /* Links */
    .admin-dashboard-link {
        color: var(--admin-accent);
        font-weight: 700;
        transition: color 0.2s;
    }

    .admin-dashboard-link:hover {
        color: var(--admin-accent-hover);
    }

    /* Shell backgrounds */
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-shell-bg {
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.055), transparent 28rem),
            #F8FAFC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-shell-bg {
        background:
            radial-gradient(circle at top right, rgba(212, 168, 67, 0.055), transparent 30rem),
            #05070C !important;
    }

    /* Calendar */
    .admin-calendar-day {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 0.75rem;
        transition: all 0.2s;
    }

    .admin-calendar-day-muted {
        opacity: 0.46;
    }

    .admin-calendar-rental {
        color: var(--admin-accent);
        font-weight: 800;
    }

    .admin-action-disabled {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
        color: var(--admin-subtle);
    }
</style>
@endpush

@section('content')
    @php
        $adminDashboardCopy = __('ui.admin_dashboard');
        
        $statusBadge = fn (?string $status) => match ($status) {
            'lunas' => ['label' => $adminDashboardCopy['statuses']['ready_pickup'], 'class' => 'status-chip-info'],
            'barang_diambil' => ['label' => $adminDashboardCopy['statuses']['on_rent'], 'class' => 'status-chip-warning'],
            'barang_kembali' => ['label' => $adminDashboardCopy['statuses']['returned'], 'class' => 'status-chip-success'],
            'barang_rusak' => ['label' => $adminDashboardCopy['statuses']['damaged'], 'class' => 'status-chip-danger'],
            default => ['label' => strtoupper((string) $status), 'class' => 'status-chip-muted'],
        };
        
        $rentalCalendar = $rentalCalendar ?? [];
        $calendarDays = collect($rentalCalendar['days'] ?? []);
        $calendarBaseQuery = request()->except(['calendar_month', 'page']);
        $financialSummary = $financialSummary ?? [];
        $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
        $isPaginator = $operationalOrders instanceof \Illuminate\Pagination\AbstractPaginator;
        $ordersCollection = $isPaginator ? $operationalOrders->getCollection() : collect($operationalOrders ?? []);
    @endphp

    <div class="admin-dashboard-page space-y-5 sm:space-y-6">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- SECTION A: Operational Overview (4-card grid) --}}
        <section class="admin-overview-grid">
            <article class="admin-dashboard-card admin-overview-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-sky">
                    {{ $adminDashboardCopy['stats']['ready_pickup'] }}
                </p>
                <div class="admin-overview-card-main">
                    <p class="admin-overview-value">
                        {{ (int) ($summary['ready_pickup'] ?? 0) }}
                    </p>
                    <p class="admin-overview-desc">
                        {{ $adminDashboardCopy['stats_desc']['ready_pickup'] }}
                    </p>
                </div>
            </article>

            <article class="admin-dashboard-card admin-overview-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-amber">
                    {{ $adminDashboardCopy['stats']['currently_rented'] }}
                </p>
                <div class="admin-overview-card-main">
                    <p class="admin-overview-value">
                        {{ (int) ($summary['on_rent'] ?? 0) }}
                    </p>
                    <p class="admin-overview-desc">
                        {{ $adminDashboardCopy['stats_desc']['currently_rented'] }}
                    </p>
                </div>
            </article>

            <article class="admin-dashboard-card admin-overview-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-emerald">
                    {{ $adminDashboardCopy['stats']['returned'] }}
                </p>
                <div class="admin-overview-card-main">
                    <p class="admin-overview-value">
                        {{ (int) ($summary['returned'] ?? 0) }}
                    </p>
                    <p class="admin-overview-desc">
                        {{ $adminDashboardCopy['stats_desc']['returned'] }}
                    </p>
                </div>
            </article>

            <article class="admin-dashboard-card admin-overview-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-rose">
                    {{ $adminDashboardCopy['stats']['damaged_case'] }}
                </p>
                <div class="admin-overview-card-main">
                    <p class="admin-overview-value">
                        {{ (int) ($summary['damaged'] ?? 0) }}
                    </p>
                    <p class="admin-overview-desc">
                        {{ $adminDashboardCopy['stats_desc']['damaged_case'] }}
                    </p>
                </div>
            </article>
        </section>

        {{-- SECTION B: Finance Overview --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-dashboard-card admin-finance-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-emerald">{{ $adminDashboardCopy['finance']['cash_in'] }}</p>
                <p class="admin-finance-value">{{ $formatIdr($financialSummary['cash_in'] ?? 0) }}</p>
                <p class="admin-finance-desc">{{ $adminDashboardCopy['finance']['cash_in_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card admin-finance-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-sky">{{ $adminDashboardCopy['finance']['rental_revenue'] }}</p>
                <p class="admin-finance-value">{{ $formatIdr($financialSummary['revenue'] ?? 0) }}</p>
                <p class="admin-finance-desc">{{ $adminDashboardCopy['finance']['rental_revenue_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card admin-finance-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-amber">{{ $adminDashboardCopy['finance']['collected_tax'] }}</p>
                <p class="admin-finance-value">{{ $formatIdr($financialSummary['tax'] ?? 0) }}</p>
                <p class="admin-finance-desc">{{ $adminDashboardCopy['finance']['collected_tax_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card admin-finance-card p-5">
                <p class="admin-dashboard-kicker admin-kicker-rose">{{ $adminDashboardCopy['finance']['damage_fee'] }}</p>
                <p class="admin-finance-value">{{ $formatIdr($financialSummary['damage_fee'] ?? 0) }}</p>
                <p class="admin-finance-desc">{{ $adminDashboardCopy['finance']['damage_fee_desc'] }}</p>
            </article>
        </section>

        {{-- SECTION C: Orders Require Action --}}
        <section class="admin-dashboard-card overflow-hidden">
            <div class="admin-dashboard-card-header flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
                <div>
                    <h2 class="text-lg font-bold admin-title">
                        {{ $adminDashboardCopy['orders_action_title'] }}
                    </h2>
                    <p class="text-xs admin-muted">
                        {{ $adminDashboardCopy['orders_action_subtitle'] }}
                    </p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="admin-dashboard-link text-sm">
                    {{ $adminDashboardCopy['manage_orders_link'] }}
                </a>
            </div>

            @if ($ordersCollection->isEmpty())
                <div class="px-5 py-8 text-sm admin-muted">
                    {{ $adminDashboardCopy['empty_operational_orders'] }}
                </div>
            @else
                <div class="divide-y admin-border">
                    @foreach ($ordersCollection as $order)
                        @php
                            $badge = $statusBadge($order->status_pesanan);
                            $itemsLabel = $order->items->pluck('equipment.name')->filter()->take(2)->implode(', ');
                            $rentalStart = $order->rental_start_date ? $order->rental_start_date->copy()->startOfDay() : null;
                            $pickupOpenAt = $rentalStart?->copy()->subDay();
                            $canConfirmPickupNow = $pickupOpenAt ? now()->greaterThanOrEqualTo($pickupOpenAt) : false;
                            $isReadyPickup = $order->status_pesanan === 'lunas';
                            $isOnRent = $order->status_pesanan === 'barang_diambil';
                            $isClosed = in_array($order->status_pesanan, ['barang_kembali', 'barang_rusak', 'selesai'], true);
                        @endphp
                        <article class="admin-order-row px-5 py-4 transition">
                            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(420px,520px)] xl:items-start">
                                <div class="min-w-0">
                                    <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="min-w-0 truncate text-base font-bold admin-title">
                                            {{ $order->order_number ?? ('ORD-' . $order->id) }}
                                        </p>
                                        <span class="status-chip shrink-0 {{ $badge['class'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    </div>
                                    <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm leading-5 admin-muted">
                                        <span>{{ $order->user?->name ?? '-' }}</span>
                                        <span class="admin-subtle">•</span>
                                        <span class="whitespace-nowrap">
                                            {{ optional($order->rental_start_date)->format('d M Y') }} – {{ optional($order->rental_end_date)->format('d M Y') }}
                                        </span>
                                    </p>
                                    @if ($itemsLabel !== '')
                                        <p class="mt-1 text-xs leading-5 admin-subtle">
                                            {{ $adminDashboardCopy['equipment_prefix'] }} {{ $itemsLabel }}@if($order->items->count() > 2) +{{ $order->items->count() - 2 }} {{ $adminDashboardCopy['more_items'] }} @endif
                                        </p>
                                    @endif
                                </div>

                                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3">
                                    @if ($isReadyPickup && $canConfirmPickupNow)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                            data-operational-confirm="{{ strtr($adminDashboardCopy['confirm_pickup_message'], [':order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pesanan" value="barang_diambil">
                                            <button class="admin-action-primary flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                                {{ $adminDashboardCopy['confirm_pickup'] }}
                                            </button>
                                        </form>
                                    @else
                                        <div class="admin-action-disabled flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                            @if ($isOnRent || $isClosed)
                                                {{ $adminDashboardCopy['already_picked_up'] }}
                                            @else
                                                {{ $adminDashboardCopy['waiting_schedule'] }}
                                            @endif
                                        </div>
                                    @endif

                                    @if ($isOnRent)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                            data-operational-confirm="{{ strtr($adminDashboardCopy['confirm_return_message'], [':order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pesanan" value="barang_kembali">
                                            <button class="admin-action-success flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                                {{ $adminDashboardCopy['confirm_return'] }}
                                            </button>
                                        </form>
                                    @else
                                        <div class="admin-action-disabled flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                            @if ($order->status_pesanan === 'barang_kembali' || $order->status_pesanan === 'selesai')
                                                {{ $adminDashboardCopy['already_returned'] }}
                                            @elseif ($order->status_pesanan === 'barang_rusak')
                                                {{ $adminDashboardCopy['marked_damaged'] }}
                                            @else
                                                {{ $adminDashboardCopy['waiting_pickup'] }}
                                            @endif
                                        </div>
                                    @endif

                                    @if ($isOnRent)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                            class="mark-damaged-form"
                                            data-order-number="{{ $order->order_number ?? ('ORD-' . $order->id) }}"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_pesanan" value="barang_rusak">
                                            <button type="submit" class="admin-action-danger flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                                {{ $adminDashboardCopy['mark_damaged'] }}
                                            </button>
                                        </form>
                                    @else
                                        <div class="admin-action-disabled flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
                                            @if ($order->status_pesanan === 'barang_rusak')
                                                {{ $adminDashboardCopy['already_marked'] }}
                                            @else
                                                {{ $adminDashboardCopy['waiting_pickup'] }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-secondary-button inline-flex rounded-xl px-3 py-1.5 text-xs font-semibold transition">
                                    {{ $adminDashboardCopy['order_detail'] }}
                                </a>
                                @if ($isReadyPickup && ! $canConfirmPickupNow && $pickupOpenAt)
                                    <p class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs text-amber-700 dark:text-amber-300">
                                        {{ __('ui.admin_dashboard.pickup_button_available_at', ['date' => $pickupOpenAt->format('d M Y')]) }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- SECTION D: Operational Support --}}
        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] xl:grid-cols-3">
            <section class="admin-dashboard-card p-5">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full admin-accent-bg"></span>
                    <h3 class="text-base font-bold admin-title">{{ $adminDashboardCopy['flow_title'] }}</h3>
                </div>
                <ul class="mt-3 space-y-2.5 text-xs admin-muted leading-relaxed">
                    <li>1. {{ $adminDashboardCopy['flow_1'] }}</li>
                    <li>2. {{ $adminDashboardCopy['flow_2'] }}</li>
                    <li>3. {{ $adminDashboardCopy['flow_3'] }}</li>
                </ul>
            </section>

            <section class="admin-dashboard-card p-5 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold admin-title">{{ $adminDashboardCopy['logs_title'] }}</h3>
                    <p class="text-[11px] admin-muted mt-1 leading-relaxed">{{ $adminDashboardCopy['logs_desc'] }}</p>
                </div>
                <div class="mt-4 flex justify-end">
                    <a href="{{ route('admin.orders.index') }}" class="admin-dashboard-link text-xs whitespace-nowrap">
                        {{ $adminDashboardCopy['open_orders'] }}
                    </a>
                </div>
            </section>

            <details class="admin-dashboard-card p-5 group">
                <summary class="cursor-pointer admin-accent-text text-sm font-bold select-none flex items-center justify-between">
                    <span>{{ $adminDashboardCopy['calendar_title'] }} ({{ $adminDashboardCopy['calendar_optional'] }})</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="mt-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-2 rounded-xl border admin-border px-2 py-1 bg-[color:var(--admin-surface-raised)]">
                            <a
                                href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['previous_month'] ?? now()->subMonth()->format('Y-m')])) }}"
                                class="admin-secondary-button inline-flex h-8 w-8 items-center justify-center rounded-lg transition"
                                aria-label="{{ $adminDashboardCopy['calendar_previous_month'] }}"
                            >
                                ←
                            </a>
                            <span class="min-w-[7rem] text-center text-xs font-semibold admin-title">{{ $rentalCalendar['month_label'] ?? now()->translatedFormat('F Y') }}</span>
                            <a
                                href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['next_month'] ?? now()->addMonth()->format('Y-m')])) }}"
                                class="admin-secondary-button inline-flex h-8 w-8 items-center justify-center rounded-lg transition"
                                aria-label="{{ $adminDashboardCopy['calendar_next_month'] }}"
                            >
                                →
                            </a>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                            <span class="rounded-full border admin-border bg-[color:var(--admin-surface-raised)] px-2 py-0.5 font-semibold admin-title">{{ $adminDashboardCopy['unit_days'] }} {{ (int) ($rentalCalendar['total_unit_days'] ?? 0) }}</span>
                            <span class="rounded-full admin-accent-soft px-2 py-0.5 font-semibold">{{ $adminDashboardCopy['peak'] }} {{ (int) ($rentalCalendar['max_daily_units'] ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 -mx-1 overflow-x-auto px-1 pb-1 sm:mx-0 sm:overflow-visible sm:px-0">
                        <div class="min-w-[420px]">
                            <div class="grid grid-cols-7 gap-1">
                                @foreach ((array) __('ui.availability_board.weekdays') as $weekday)
                                    <p class="text-center text-[10px] font-bold uppercase tracking-wider admin-subtle">{{ $weekday }}</p>
                                @endforeach
                            </div>
                            <div class="mt-1.5 grid grid-cols-7 gap-1">
                                @foreach ($calendarDays as $day)
                                    @php
                                        $hasRental = (int) ($day['total_qty'] ?? 0) > 0;
                                    @endphp
                                    <div class="admin-calendar-day p-1.5 {{ ($day['in_month'] ?? false) ? '' : 'admin-calendar-day-muted' }}">
                                        <p class="text-[10px] font-bold admin-title">{{ $day['day'] }}</p>
                                        <p class="mt-0.5 text-[10px] {{ $hasRental ? 'admin-calendar-rental' : 'admin-subtle' }}">
                                            {{ $hasRental ? ($day['total_qty'] . ' ' . $adminDashboardCopy['unit']) : '-' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </details>
        </section>

        @if ($isPaginator)
            <div class="border-t admin-border pt-4">
                {{ $operationalOrders->links() }}
            </div>
        @endif

        <!-- Modal Biaya Kerusakan -->
        <div id="damage-fee-modal" class="fixed inset-0 z-[120] hidden items-center justify-center bg-[#0A0A0B]/75 p-4" role="dialog" aria-modal="true" aria-labelledby="damage-modal-title">
            <div class="w-full max-w-md rounded-2xl border border-[#1A1A1E] bg-[#111113] p-6 shadow-2xl">
                <h3 id="damage-modal-title" class="text-base font-bold text-[#E8E8EC]">
                    {{ __('Input Biaya Kerusakan') }}
                </h3>
                <p class="mt-1 text-xs text-[#A0A0A8]">
                    {{ __('Pesanan') }}: <span id="damage-modal-order-number" class="font-semibold text-emerald-400"></span>
                </p>

                <form id="damage-fee-form" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_pesanan" value="barang_rusak">

                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="damage-additional-fee" class="block text-xs font-semibold text-[#A0A0A8]">{{ __('Biaya Kerusakan (Rp)') }}</label>
                            <input
                                type="number"
                                min="0"
                                step="1000"
                                id="damage-additional-fee"
                                name="additional_fee"
                                placeholder="Contoh: 100000"
                                class="mt-1.5 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-2 text-sm text-[#E8E8EC] placeholder-[#52525B] focus:border-[#D4A843] focus:outline-none focus:ring-1 focus:ring-[#D4A843]"
                                required
                            >
                        </div>

                        <div>
                            <label for="damage-additional-fee-note" class="block text-xs font-semibold text-[#A0A0A8]">{{ __('Catatan Kerusakan') }}</label>
                            <textarea
                                id="damage-additional-fee-note"
                                name="additional_fee_note"
                                rows="3"
                                placeholder="Contoh: Goresan lensa depan, LCD pecah"
                                class="mt-1.5 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3.5 py-2 text-sm text-[#E8E8EC] placeholder-[#52525B] focus:border-[#D4A843] focus:outline-none focus:ring-1 focus:ring-[#D4A843]"
                                required
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5">
                        <button id="close-damage-modal" type="button" class="inline-flex min-h-9 items-center justify-center rounded-xl border border-[#1A1A1E] bg-transparent px-4 py-2 text-xs font-semibold text-[#E8E8EC] transition hover:bg-[#1A1A1E]">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">
                            {{ __('Tandai Rusak & Tagih') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            // Confirm dialog handler for non-damage operations
            document.querySelectorAll('form[data-operational-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.classList.contains('mark-damaged-form')) {
                        return;
                    }
                    const message = form.dataset.operationalConfirm || '';
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });

            // Modal handlers for damaged mark
            const damageModal = document.getElementById('damage-fee-modal');
            const damageForm = document.getElementById('damage-fee-form');
            const damageOrderNumberSpan = document.getElementById('damage-modal-order-number');
            const closeDamageModalBtn = document.getElementById('close-damage-modal');

            document.querySelectorAll('form.mark-damaged-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const actionUrl = form.getAttribute('action');
                    const orderNumber = form.dataset.orderNumber || '';
                    
                    if (damageModal && damageForm) {
                        damageForm.setAttribute('action', actionUrl);
                        if (damageOrderNumberSpan) {
                            damageOrderNumberSpan.textContent = orderNumber;
                        }
                        damageModal.classList.remove('hidden');
                        damageModal.classList.add('flex');
                        
                        // Focus on fee input
                        const feeInput = document.getElementById('damage-additional-fee');
                        if (feeInput) {
                            feeInput.value = '';
                            feeInput.focus();
                        }
                        const noteInput = document.getElementById('damage-additional-fee-note');
                        if (noteInput) {
                            noteInput.value = '';
                        }
                    }
                });
            });

            const hideDamageModal = () => {
                if (damageModal) {
                    damageModal.classList.add('hidden');
                    damageModal.classList.remove('flex');
                }
            };

            closeDamageModalBtn?.addEventListener('click', hideDamageModal);
            damageModal?.addEventListener('click', (event) => {
                if (event.target === damageModal) {
                    hideDamageModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    hideDamageModal();
                }
            });
        })();
    </script>
@endpush

@extends('layouts.admin', ['activePage' => 'dashboard'])

@section('title', __('ui.admin_dashboard.title'))
@section('page_title', __('ui.admin_dashboard.page_title'))

@push('head')
<style>
    .admin-dashboard-page {
        color: var(--admin-text);
    }

    .admin-dashboard-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-dashboard-card {
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.18);
    }

    .admin-dashboard-kicker {
        color: var(--admin-muted);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-dashboard-value {
        color: var(--admin-text);
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    .admin-dashboard-link {
        color: var(--admin-accent);
        font-weight: 700;
        transition: color 0.2s;
    }

    .admin-dashboard-link:hover {
        color: var(--admin-accent-hover);
    }

    .admin-action-disabled {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
        color: var(--admin-subtle);
    }

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
        $resolvedActionableCount = $actionableCount ?? $ordersCollection->filter(fn ($order) => in_array((string) ($order->status_pesanan ?? ''), ['lunas', 'barang_diambil'], true))->count();
    @endphp

    <div class="space-y-6 admin-dashboard-page">
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

        {{-- Section 1: Stats & Priority --}}
        <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="admin-dashboard-card p-5 flex flex-col justify-between min-h-[140px]">
                    <p class="admin-dashboard-kicker text-sky-500 dark:text-sky-300">{{ $adminDashboardCopy['stats']['ready_pickup'] }}</p>
                    <div class="mt-2">
                        <p class="admin-dashboard-value text-3xl">{{ (int) ($summary['ready_pickup'] ?? 0) }}</p>
                        <p class="text-[10px] admin-muted mt-1">Paid and ready for pickup</p>
                    </div>
                </article>
                <article class="admin-dashboard-card p-5 flex flex-col justify-between min-h-[140px]">
                    <p class="admin-dashboard-kicker text-amber-500 dark:text-amber-300">{{ $adminDashboardCopy['stats']['currently_rented'] }}</p>
                    <div class="mt-2">
                        <p class="admin-dashboard-value text-3xl">{{ (int) ($summary['on_rent'] ?? 0) }}</p>
                        <p class="text-[10px] admin-muted mt-1">Equipment currently out</p>
                    </div>
                </article>
                <article class="admin-dashboard-card p-5 flex flex-col justify-between min-h-[140px]">
                    <p class="admin-dashboard-kicker text-emerald-500 dark:text-emerald-300">{{ $adminDashboardCopy['stats']['returned'] }}</p>
                    <div class="mt-2">
                        <p class="admin-dashboard-value text-3xl">{{ (int) ($summary['returned'] ?? 0) }}</p>
                        <p class="text-[10px] admin-muted mt-1">Returned completed orders</p>
                    </div>
                </article>
                <article class="admin-dashboard-card p-5 flex flex-col justify-between min-h-[140px]">
                    <p class="admin-dashboard-kicker text-rose-500 dark:text-rose-300">{{ $adminDashboardCopy['stats']['damaged_case'] }}</p>
                    <div class="mt-2">
                        <p class="admin-dashboard-value text-3xl">{{ (int) ($summary['damaged'] ?? 0) }}</p>
                        <p class="text-[10px] admin-muted mt-1">Returned damaged cases</p>
                    </div>
                </article>
            </div>

            <article class="admin-dashboard-card p-5 flex flex-col justify-between min-h-[140px] border-l-4 border-l-[color:var(--admin-accent)]">
                <div>
                    <p class="admin-accent-text text-xs font-black uppercase tracking-[0.22em]">
                        {{ $adminDashboardCopy['priority_title'] }}
                    </p>
                    <p class="mt-2 admin-dashboard-value text-4xl">{{ $resolvedActionableCount }}</p>
                    <p class="mt-2 text-xs leading-5 admin-muted">
                        {{ $adminDashboardCopy['priority_desc'] }}
                    </p>
                </div>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="admin-accent-bg inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition">
                        {{ $adminDashboardCopy['open_all_orders'] }}
                    </a>
                    <a href="{{ route('admin.equipments.index') }}" class="admin-secondary-button inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition">
                        {{ $adminDashboardCopy['check_equipment_stock'] }}
                    </a>
                    <a href="{{ route('availability.board') }}" class="admin-secondary-button inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition">
                        {{ $adminDashboardCopy['availability_calendar'] }}
                    </a>
                </div>
            </article>
        </section>

        {{-- Section 2: Finances --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-dashboard-card p-5">
                <p class="admin-dashboard-kicker">{{ $adminDashboardCopy['finance']['cash_in'] }}</p>
                <p class="mt-3 text-2xl font-black text-[color:var(--admin-text)]">{{ $formatIdr($financialSummary['cash_in'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-5 admin-muted">{{ $adminDashboardCopy['finance']['cash_in_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card p-5">
                <p class="admin-dashboard-kicker text-sky-500 dark:text-sky-300">{{ $adminDashboardCopy['finance']['rental_revenue'] }}</p>
                <p class="mt-3 text-2xl font-black text-[color:var(--admin-text)]">{{ $formatIdr($financialSummary['revenue'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-5 admin-muted">{{ $adminDashboardCopy['finance']['rental_revenue_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card p-5">
                <p class="admin-dashboard-kicker text-amber-500 dark:text-amber-300">{{ $adminDashboardCopy['finance']['collected_tax'] }}</p>
                <p class="mt-3 text-2xl font-black text-[color:var(--admin-text)]">{{ $formatIdr($financialSummary['tax'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-5 admin-muted">{{ $adminDashboardCopy['finance']['collected_tax_desc'] }}</p>
            </article>
            <article class="admin-dashboard-card p-5">
                <p class="admin-dashboard-kicker text-rose-500 dark:text-rose-300">{{ $adminDashboardCopy['finance']['damage_fee'] }}</p>
                <p class="mt-3 text-2xl font-black text-[color:var(--admin-text)]">{{ $formatIdr($financialSummary['damage_fee'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-5 admin-muted">{{ $adminDashboardCopy['finance']['damage_fee_desc'] }}</p>
            </article>
        </section>

        {{-- Section 3: Orders action & sidebar information --}}
        <section class="grid gap-5 2xl:grid-cols-[minmax(0,1fr)_380px]">
            <section class="admin-dashboard-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b admin-border px-5 py-4 bg-[color:var(--admin-surface-raised)]/30">
                    <div>
                        <h2 class="text-lg font-bold admin-title">{{ $adminDashboardCopy['orders_action_title'] }}</h2>
                        <p class="text-xs admin-muted">{{ $adminDashboardCopy['orders_action_subtitle'] }}</p>
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
                            <article class="px-5 py-4 transition hover:bg-[color:var(--admin-surface-raised)]">
                                <div class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_460px] 2xl:items-start">
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="min-w-0 truncate text-base font-bold admin-title">
                                                {{ $order->order_number ?? ('ORD-' . $order->id) }}
                                            </p>
                                            <span class="status-chip shrink-0 {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                        </div>
                                        <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm leading-5 admin-muted">
                                            <span>{{ $order->user?->name ?? '-' }}</span>
                                            <span class="admin-subtle">•</span>
                                            <span class="whitespace-nowrap">{{ optional($order->rental_start_date)->format('d M Y') }} – {{ optional($order->rental_end_date)->format('d M Y') }}</span>
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
                                                data-operational-confirm="{{ strtr($adminDashboardCopy['confirm_damage_message'], [':order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_rusak">
                                                <button class="admin-action-danger flex min-h-10 w-full items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-semibold">
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

            <div class="space-y-4">
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

                <section class="admin-dashboard-card p-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold admin-title">{{ $adminDashboardCopy['logs_title'] }}</h3>
                        <p class="text-[11px] admin-muted mt-1 leading-relaxed">{{ $adminDashboardCopy['logs_desc'] }}</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="admin-dashboard-link text-xs whitespace-nowrap">
                        {{ $adminDashboardCopy['open_orders'] }}
                    </a>
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
            </div>
        </section>

        @if ($isPaginator)
            <div class="border-t admin-border pt-4">
                {{ $operationalOrders->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('form[data-operational-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const message = form.dataset.operationalConfirm || '';
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });
        })();
    </script>
@endpush

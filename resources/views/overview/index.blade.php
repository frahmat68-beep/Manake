@extends('layouts.app')

@section('title', __('ui.nav.my_orders'))
@section('page_title', __('ui.nav.my_orders'))

@php
    use App\Models\Order;
    use Illuminate\Support\Facades\URL;

    $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $historyCopy = __('ui.booking_history');

    $bookingTitle = $historyCopy['title'];
    $bookingSubtitle = $historyCopy['subtitle'];
    $catalogLabel = $historyCopy['browse_catalog'];

    $statusTone = function (string $type, ?string $status): string {
        $normalized = strtolower((string) $status);

        if ($type === 'payment') {
            return match ($normalized) {
                Order::PAYMENT_PAID, 'settlement', 'success' => 'history-status-paid',
                Order::PAYMENT_FAILED, Order::PAYMENT_EXPIRED, Order::STATUS_CANCELLED => 'history-status-danger',
                Order::PAYMENT_REFUNDED, Order::STATUS_REFUNDED => 'history-status-neutral',
                default => 'history-status-warning',
            };
        }

        return match ($normalized) {
            Order::STATUS_COMPLETED, Order::STATUS_RETURNED_OK => 'history-status-paid',
            Order::STATUS_CANCELLED, Order::STATUS_EXPIRED, Order::STATUS_RETURNED_DAMAGED, Order::STATUS_RETURNED_LOST, Order::STATUS_OVERDUE_DAMAGE_INVOICE => 'history-status-danger',
            Order::STATUS_READY_PICKUP, Order::STATUS_ON_RENT => 'history-status-warning',
            default => 'history-status-neutral',
        };
    };

    $paymentLabel = function (?string $status) use ($historyCopy): string {
        return match (strtolower((string) $status)) {
            Order::PAYMENT_PAID, 'settlement', 'success' => $historyCopy['payment_paid'],
            Order::PAYMENT_FAILED => $historyCopy['payment_failed'],
            Order::PAYMENT_EXPIRED => $historyCopy['payment_expired'],
            Order::PAYMENT_REFUNDED => $historyCopy['payment_refunded'],
            default => $historyCopy['payment_pending'],
        };
    };

    $rentalLabel = function (?string $status) use ($historyCopy): string {
        return match (strtolower((string) $status)) {
            Order::STATUS_PENDING_PAYMENT => $historyCopy['rental_waiting'],
            Order::STATUS_PROCESSING => $historyCopy['rental_confirmed'],
            Order::STATUS_READY_PICKUP => $historyCopy['rental_confirmed'],
            Order::STATUS_ON_RENT => $historyCopy['rental_on_rent'],
            Order::STATUS_RETURNED_OK => $historyCopy['rental_returned'],
            Order::STATUS_COMPLETED => $historyCopy['rental_completed'],
            Order::STATUS_CANCELLED => $historyCopy['rental_cancelled'],
            Order::STATUS_EXPIRED => $historyCopy['rental_cancelled'],
            Order::STATUS_REFUNDED => $historyCopy['rental_refunded'],
            Order::STATUS_RETURNED_DAMAGED => $historyCopy['rental_returned'],
            Order::STATUS_RETURNED_LOST => $historyCopy['rental_returned'],
            Order::STATUS_OVERDUE_DAMAGE_INVOICE => $historyCopy['rental_returned'],
            default => $historyCopy['rental_waiting'],
        };
    };

    $canViewInvoice = static fn ($order) => ($order->status_pembayaran ?? Order::PAYMENT_PENDING) === Order::PAYMENT_PAID && ! $order->hasOutstandingDamageFee();
    $canPayOrder = static fn ($order) => in_array((string) ($order->status_pembayaran ?? Order::PAYMENT_PENDING), [Order::PAYMENT_PENDING], true);
    $canRefreshStatus = static fn ($order) => in_array((string) ($order->status_pembayaran ?? Order::PAYMENT_PENDING), [Order::PAYMENT_PENDING], true);
    $canRescheduleOrder = static fn ($order) => in_array((string) ($order->status_pesanan ?? ''), [
        Order::STATUS_PENDING_PAYMENT,
        Order::STATUS_PROCESSING,
        Order::STATUS_READY_PICKUP,
    ], true);
    $canCancelOrder = static fn ($order) => (string) ($order->status_pesanan ?? '') === Order::STATUS_PENDING_PAYMENT;
@endphp

@push('head')
    <style>
        .history-enter {
            animation: history-enter 520ms ease-out both;
        }

        .history-card-in {
            animation: history-card-in 520ms ease-out both;
        }

        @keyframes history-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes history-card-in {
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
            .history-enter,
            .history-card-in {
                animation: none !important;
            }
        }

        .history-page {
            --history-accent: #D4A843;
            --history-accent-hover: #E0BA5D;
            --history-accent-text: #0A0A0B;
            --history-accent-soft: rgba(212, 168, 67, 0.12);
            --history-accent-border: rgba(212, 168, 67, 0.28);

            --history-bg: #0A0A0B;
            --history-surface: #111113;
            --history-surface-soft: rgba(17, 17, 19, 0.72);
            --history-surface-muted: #0A0A0B;
            --history-border: #1A1A1E;
            --history-text: #E8E8EC;
            --history-muted: #A0A0A8;
            --history-subtle: #7C7C84;
        }

        html[data-theme-resolved="light"] .history-page {
            --history-accent: #2563EB;
            --history-accent-hover: #1D4ED8;
            --history-accent-text: #FFFFFF;
            --history-accent-soft: rgba(37, 99, 235, 0.10);
            --history-accent-border: rgba(37, 99, 235, 0.24);

            --history-bg: #F8FAFC;
            --history-surface: #FFFFFF;
            --history-surface-soft: rgba(255, 255, 255, 0.92);
            --history-surface-muted: #F8FAFC;
            --history-border: #E5E7EB;
            --history-text: #111827;
            --history-muted: #4B5563;
            --history-subtle: #6B7280;
        }

        .history-page-bg {
            background-color: var(--history-bg) !important;
            color: var(--history-text) !important;
        }

        .history-card {
            background: var(--history-surface-soft) !important;
            border-color: var(--history-border) !important;
            color: var(--history-text) !important;
        }

        .history-card-solid {
            background: var(--history-surface) !important;
            border-color: var(--history-border) !important;
            color: var(--history-text) !important;
        }

        .history-inner {
            background: var(--history-surface-muted) !important;
            border-color: var(--history-border) !important;
            color: var(--history-text) !important;
        }

        .history-title {
            color: var(--history-text) !important;
        }

        .history-muted {
            color: var(--history-muted) !important;
        }

        .history-subtle {
            color: var(--history-subtle) !important;
        }

        .history-border {
            border-color: var(--history-border) !important;
        }

        .history-accent-text {
            color: var(--history-accent) !important;
        }

        .history-accent-bg {
            background: var(--history-accent) !important;
            background-color: var(--history-accent) !important;
            color: var(--history-accent-text) !important;
            border-color: var(--history-accent) !important;
        }

        .history-accent-bg:hover {
            background: var(--history-accent-hover) !important;
            background-color: var(--history-accent-hover) !important;
        }

        .history-accent-soft {
            background: var(--history-accent-soft) !important;
            border-color: var(--history-accent-border) !important;
            color: var(--history-accent) !important;
        }

        .history-accent-dot {
            background-color: var(--history-accent) !important;
        }

        .history-secondary-button {
            background: var(--history-surface) !important;
            border: 1px solid var(--history-border) !important;
            color: var(--history-text) !important;
        }

        .history-secondary-button:hover {
            border-color: var(--history-accent-border) !important;
            color: var(--history-accent) !important;
        }

        .history-accent-link:hover {
            color: var(--history-accent) !important;
        }

        html[data-theme-resolved="light"] .history-page .history-card,
        html[data-theme-resolved="light"] .history-page .history-card-solid {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
        }

        .history-status-neutral {
            border-color: var(--history-border) !important;
            background: var(--history-surface-muted) !important;
            color: var(--history-muted) !important;
        }

        .history-status-paid {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .history-status-warning {
            border-color: rgba(245, 158, 11, 0.28) !important;
            background: #FFFBEB !important;
            color: #B45309 !important;
        }

        .history-status-danger {
            border-color: rgba(244, 63, 94, 0.28) !important;
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }

        html[data-theme-resolved="dark"] .history-status-paid {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .history-status-warning {
            background: rgba(245, 158, 11, 0.12) !important;
            color: #FDE68A !important;
        }

        html[data-theme-resolved="dark"] .history-status-danger {
            background: rgba(244, 63, 94, 0.12) !important;
            color: #FDA4AF !important;
        }
    </style>
@endpush

@section('content')
    <div class="history-page history-page-bg min-h-screen">
        <div class="mx-auto w-full max-w-7xl px-4 py-8 pb-24 sm:px-6 lg:px-8">
            <header class="history-card history-enter rounded-3xl border p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.30)] sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <h2 class="history-title text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ $bookingTitle }}
                        </h2>
                        <p class="history-muted max-w-2xl text-sm leading-6 sm:text-base">
                            {{ $bookingSubtitle }}
                        </p>
                    </div>

                    <a
                        href="{{ route('catalog') }}"
                        class="history-accent-bg inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[var(--history-accent-soft)]"
                    >
                        {{ $catalogLabel }}
                    </a>
                </div>
            </header>

            @if (session('error') || session('success'))
                <div class="mt-6 space-y-3">
                    @if (session('error'))
                        <div class="history-card-in rounded-2xl border border-rose-400/20 bg-rose-500/8 px-4 py-3 text-sm font-medium text-rose-200">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-rose-300"></span>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="history-card-in rounded-2xl border border-emerald-400/20 bg-emerald-500/8 px-4 py-3 text-sm font-medium text-emerald-200">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="history-card history-card-in rounded-3xl border p-5" style="animation-delay: 40ms">
                    <p class="text-sm font-medium history-muted">{{ $historyCopy['stats_total_label'] }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight history-title">{{ $stats['total_booking'] ?? 0 }}</p>
                    <p class="mt-2 text-xs history-subtle">{{ $historyCopy['stats_total_desc'] }}</p>
                </article>

                <article class="history-card history-card-in rounded-3xl border p-5" style="animation-delay: 80ms">
                    <p class="text-sm font-medium history-muted">{{ $historyCopy['stats_active_label'] }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight history-accent-text">{{ $stats['active_rental'] ?? 0 }}</p>
                    <p class="mt-2 text-xs history-subtle">{{ $historyCopy['stats_active_desc'] }}</p>
                </article>

                <article class="history-card history-card-in rounded-3xl border p-5" style="animation-delay: 120ms">
                    <p class="text-sm font-medium history-muted">{{ $historyCopy['stats_finished_label'] }}</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-500">{{ $stats['completed'] ?? 0 }}</p>
                    <p class="mt-2 text-xs history-subtle">{{ $historyCopy['stats_finished_desc'] }}</p>
                </article>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-start">
                <section class="history-card history-card-in rounded-3xl border p-6" style="animation-delay: 160ms">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold tracking-tight history-title">{{ $historyCopy['active_title'] }}</h3>
                            <p class="mt-1 text-sm history-muted">{{ $historyCopy['active_subtitle'] }}</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($activeRentals as $order)
                            @php
                                $orderNumber = $order->order_number ?? ('ORD-' . $order->id);
                                $durationDays = 1;
                                if ($order->rental_start_date && $order->rental_end_date && $order->rental_end_date->gte($order->rental_start_date)) {
                                    $durationDays = $order->rental_start_date->diffInDays($order->rental_end_date) + 1;
                                }
                                $itemSummary = $order->items?->pluck('equipment.name')->filter()->take(2)->implode(', ');
                                $itemCount = (int) ($order->items?->sum('qty') ?? 0);
                                $canOpenInvoice = $canViewInvoice($order);
                                $orderRouteKey = (string) ($order->order_number ?: $order->midtrans_order_id ?: '');
                                $signedInvoiceUrl = ($canOpenInvoice && $orderRouteKey !== '')
                                    ? URL::temporarySignedRoute('account.orders.receipt', now()->addMinutes(30), ['order' => $orderRouteKey])
                                    : null;

                                $unitLabel = $itemCount === 1 ? $historyCopy['unit_singular'] : $historyCopy['unit_plural'];
                                $dayLabel = $durationDays === 1 ? $historyCopy['day_singular'] : $historyCopy['day_plural'];
                            @endphp

                            <article class="history-inner history-card-in rounded-3xl border p-5" style="animation-delay: {{ min(($loop->index + 4) * 50, 320) }}ms">
                                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="min-w-0 flex-1 space-y-3">
                                        <div class="space-y-1">
                                            <p class="text-xs font-semibold tracking-[0.14em] history-accent-text">{{ $historyCopy['order_label'] }}</p>
                                            <h4 class="break-all text-lg font-semibold history-title">{{ $orderNumber }}</h4>
                                            <p class="text-sm leading-6 history-muted">
                                                {{ $itemSummary ?: $historyCopy['equipment_summary_empty'] }}
                                                @if ($itemCount > 0)
                                                    <span class="history-subtle">• {{ $itemCount }} {{ $unitLabel }}</span>
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 text-sm">
                                            <span class="inline-flex items-center rounded-full border history-card-solid px-3 py-1.5 history-title">
                                                {{ optional($order->rental_start_date)->translatedFormat('d M Y') }} — {{ optional($order->rental_end_date)->translatedFormat('d M Y') }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full border history-card-solid px-3 py-1.5 history-title">
                                                {{ $durationDays }} {{ $dayLabel }}
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone('payment', $order->status_pembayaran ?? Order::PAYMENT_PENDING) }}">
                                                {{ $paymentLabel($order->status_pembayaran ?? Order::PAYMENT_PENDING) }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone('rental', $order->status_pesanan ?? Order::STATUS_PENDING_PAYMENT) }}">
                                                {{ $rentalLabel($order->status_pesanan ?? Order::STATUS_PENDING_PAYMENT) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-full xl:w-[300px] xl:shrink-0">
                                        <div class="rounded-2xl border history-card-solid p-4">
                                            <div class="flex items-baseline justify-between border-b history-border pb-3">
                                                <p class="text-xs font-medium history-muted">{{ $historyCopy['total_label'] }}</p>
                                                <p class="text-2xl font-bold tracking-tight history-title">
                                                    {{ $formatIdr($order->grand_total ?? $order->total_amount) }}
                                                </p>
                                            </div>

                                            @php
                                                $hasRefresh = $canRefreshStatus($order);
                                                $hasCancel = $canCancelOrder($order);
                                                $bothExist = $hasRefresh && $hasCancel;
                                            @endphp

                                            <div class="mt-4 grid grid-cols-2 gap-2">
                                                <a
                                                    href="{{ route('account.orders.show', $order) }}"
                                                    class="col-span-2 inline-flex w-full min-h-[2.5rem] items-center justify-center rounded-xl border history-secondary-button history-accent-link px-3.5 py-2 text-center text-sm font-semibold transition"
                                                >
                                                    {{ $historyCopy['details_reschedule'] }}
                                                </a>

                                                @if ($canPayOrder($order))
                                                    <a
                                                        href="{{ route('booking.pay', $order) }}"
                                                        class="col-span-2 inline-flex w-full min-h-[2.5rem] items-center justify-center rounded-xl history-accent-bg px-3.5 py-2 text-center text-sm font-semibold transition"
                                                    >
                                                        {{ $historyCopy['pay_now'] }}
                                                    </a>
                                                @endif

                                                @if ($hasRefresh)
                                                    <form method="POST" action="{{ route('payments.refresh-status', $order) }}" class="{{ $bothExist ? 'col-span-1' : 'col-span-2' }} w-full">
                                                        @csrf
                                                        <button
                                                            type="submit"
                                                            class="inline-flex w-full min-h-[2.5rem] items-center justify-center rounded-xl border history-secondary-button history-accent-link px-3.5 py-2 text-center text-sm font-semibold transition"
                                                        >
                                                            {{ $historyCopy['refresh_status'] }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($hasCancel)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('account.orders.cancel', $order) }}"
                                                        onsubmit="return confirm('{{ $historyCopy['cancel_confirm'] }}');"
                                                        class="{{ $bothExist ? 'col-span-1' : 'col-span-2' }} w-full"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="inline-flex w-full min-h-[2.5rem] items-center justify-center rounded-xl border border-rose-400/25 bg-rose-500/5 px-3.5 py-2 text-center text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10"
                                                        >
                                                            {{ $historyCopy['cancel'] }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($signedInvoiceUrl)
                                                    <a
                                                        href="{{ $signedInvoiceUrl }}"
                                                        class="col-span-2 inline-flex w-full min-h-[2.5rem] items-center justify-center rounded-xl border history-secondary-button history-accent-link px-3.5 py-2 text-center text-sm font-semibold transition"
                                                    >
                                                        {{ $historyCopy['invoice'] }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed history-inner px-6 py-10 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border history-card-solid">
                                    <span class="h-2.5 w-2.5 rounded-full history-accent-dot"></span>
                                </div>
                                <h4 class="mt-4 text-lg font-semibold history-title">{{ $historyCopy['empty_active_title'] }}</h4>
                                <p class="mt-2 text-sm leading-6 history-muted">{{ $historyCopy['empty_active_subtitle'] }}</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="history-card history-card-in self-start rounded-3xl border p-6" style="animation-delay: 220ms">
                    <div>
                        <h3 class="text-xl font-bold tracking-tight history-title">{{ $historyCopy['recent_title'] }}</h3>
                        <p class="mt-1 text-sm history-muted">{{ $historyCopy['recent_subtitle'] }}</p>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentBookings->take(2) as $order)
                            @php
                                $orderNumber = $order->order_number ?? ('ORD-' . $order->id);
                            @endphp
                            <article class="history-inner history-card-in rounded-2xl border p-4" style="animation-delay: {{ min(($loop->index + 8) * 40, 340) }}ms">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-semibold history-title">{{ $orderNumber }}</p>
                                        <p class="mt-1 text-xs leading-5 history-muted">
                                            {{ optional($order->created_at)->translatedFormat('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone('payment', $order->status_pembayaran ?? Order::PAYMENT_PENDING) }}">
                                        {{ $paymentLabel($order->status_pembayaran ?? Order::PAYMENT_PENDING) }}
                                    </span>
                                </div>

                                <div class="mt-4 flex items-end justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-medium history-muted">{{ $historyCopy['total_label'] }}</p>
                                        <p class="mt-1 text-lg font-semibold history-title">
                                            {{ $formatIdr($order->grand_total ?? $order->total_amount) }}
                                        </p>
                                        <p class="mt-1 text-xs history-muted">
                                            {{ $rentalLabel($order->status_pesanan ?? Order::STATUS_PENDING_PAYMENT) }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('account.orders.show', $order) }}"
                                        class="inline-flex items-center justify-center rounded-xl border history-secondary-button history-accent-link px-3.5 py-2 text-sm font-semibold transition"
                                    >
                                        {{ $historyCopy['details'] }}
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed history-inner px-6 py-10 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border history-card-solid">
                                    <span class="h-2.5 w-2.5 rounded-full history-accent-dot"></span>
                                </div>
                                <h4 class="mt-4 text-lg font-semibold history-title">{{ $historyCopy['empty_recent_title'] }}</h4>
                                <p class="mt-2 text-sm leading-6 history-muted">{{ $historyCopy['empty_recent_subtitle'] }}</p>
                            </div>
                        @endforelse
                    </div>

                    <p class="pt-3 text-xs history-subtle">
                        {{ $historyCopy['pagination_note'] }}
                    </p>
                </section>
            </div>

            @if (isset($orders) && method_exists($orders, 'links'))
                <div class="mt-6 pr-20 sm:pr-0">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

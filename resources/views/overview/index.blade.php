@extends('layouts.app')

@section('title', __('ui.nav.my_orders'))
@section('page_title', __('ui.nav.my_orders'))

@php
    use App\Models\Order;
    use Illuminate\Support\Facades\URL;

    $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $bookingTitle = __('Riwayat Sewa');
    $bookingSubtitle = __('Pantau pesanan, pembayaran, dan status sewa alat Anda.');
    $catalogLabel = __('Jelajahi Katalog');

    $statusTone = function (string $type, ?string $status): string {
        $normalized = strtolower((string) $status);

        if ($type === 'payment') {
            return match ($normalized) {
                Order::PAYMENT_PAID, 'settlement', 'success' => 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200',
                Order::PAYMENT_FAILED, Order::PAYMENT_EXPIRED, Order::STATUS_CANCELLED => 'border-rose-400/20 bg-rose-500/10 text-rose-200',
                Order::PAYMENT_REFUNDED, Order::STATUS_REFUNDED => 'border-white/10 bg-white/[0.04] text-[#C8C8CE]',
                default => 'border-amber-400/20 bg-amber-500/10 text-amber-200',
            };
        }

        return match ($normalized) {
            Order::STATUS_COMPLETED, Order::STATUS_RETURNED_OK => 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200',
            Order::STATUS_CANCELLED, Order::STATUS_EXPIRED, Order::STATUS_RETURNED_DAMAGED, Order::STATUS_RETURNED_LOST, Order::STATUS_OVERDUE_DAMAGE_INVOICE => 'border-rose-400/20 bg-rose-500/10 text-rose-200',
            Order::STATUS_READY_PICKUP, Order::STATUS_ON_RENT => 'border-amber-400/20 bg-amber-500/10 text-amber-200',
            default => 'border-white/10 bg-white/[0.04] text-[#C8C8CE]',
        };
    };

    $paymentLabel = function (?string $status): string {
        return match (strtolower((string) $status)) {
            Order::PAYMENT_PAID, 'settlement', 'success' => __('Lunas'),
            Order::PAYMENT_FAILED => __('Gagal'),
            Order::PAYMENT_EXPIRED => __('Kedaluwarsa'),
            Order::PAYMENT_REFUNDED => __('Refund'),
            default => __('Menunggu Pembayaran'),
        };
    };

    $rentalLabel = function (?string $status): string {
        return match (strtolower((string) $status)) {
            Order::STATUS_PENDING_PAYMENT => __('Menunggu'),
            Order::STATUS_PROCESSING => __('Terkonfirmasi'),
            Order::STATUS_READY_PICKUP => __('Terkonfirmasi'),
            Order::STATUS_ON_RENT => __('Sedang Disewa'),
            Order::STATUS_RETURNED_OK => __('Dikembalikan'),
            Order::STATUS_COMPLETED => __('Selesai'),
            Order::STATUS_CANCELLED => __('Dibatalkan'),
            Order::STATUS_EXPIRED => __('Dibatalkan'),
            Order::STATUS_REFUNDED => __('Refund'),
            Order::STATUS_RETURNED_DAMAGED => __('Dikembalikan'),
            Order::STATUS_RETURNED_LOST => __('Dikembalikan'),
            Order::STATUS_OVERDUE_DAMAGE_INVOICE => __('Dikembalikan'),
            default => __('Menunggu'),
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
    </style>
@endpush

@section('content')
    <header class="history-enter rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <h2 class="text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">
                    {{ $bookingTitle }}
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-[#A0A0A8] sm:text-base">
                    {{ $bookingSubtitle }}
                </p>
            </div>

            <a
                href="{{ route('catalog') }}"
                class="inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-5 py-3 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40"
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
        <article class="history-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-5" style="animation-delay: 40ms">
            <p class="text-sm font-medium text-[#A0A0A8]">{{ __('Total Riwayat') }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-[#E8E8EC]">{{ $stats['total_booking'] ?? 0 }}</p>
            <p class="mt-2 text-xs text-[#7C7C84]">{{ __('Jumlah seluruh pesanan yang pernah dibuat.') }}</p>
        </article>

        <article class="history-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-5" style="animation-delay: 80ms">
            <p class="text-sm font-medium text-[#A0A0A8]">{{ __('Rental Aktif') }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-[#D4A843]">{{ $stats['active_rental'] ?? 0 }}</p>
            <p class="mt-2 text-xs text-[#7C7C84]">{{ __('Pesanan yang masih berjalan atau menunggu proses.') }}</p>
        </article>

        <article class="history-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-5" style="animation-delay: 120ms">
            <p class="text-sm font-medium text-[#A0A0A8]">{{ __('Selesai') }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-300">{{ $stats['completed'] ?? 0 }}</p>
            <p class="mt-2 text-xs text-[#7C7C84]">{{ __('Pesanan yang sudah selesai dan ditutup.') }}</p>
        </article>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(360px,0.85fr)] lg:items-start">
        <section class="history-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6" style="animation-delay: 160ms">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Rental Aktif') }}</h3>
                    <p class="mt-1 text-sm text-[#A0A0A8]">{{ __('Pesanan yang sedang berjalan atau masih menunggu pembayaran.') }}</p>
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
                    @endphp

                    <article class="history-card-in rounded-3xl border border-[#1A1A1E] bg-[#0A0A0B]/75 p-5" style="animation-delay: {{ min(($loop->index + 4) * 50, 320) }}ms">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1 space-y-3">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold tracking-[0.14em] text-[#D4A843]">{{ __('Order') }}</p>
                                    <h4 class="break-all text-lg font-semibold text-[#E8E8EC]">{{ $orderNumber }}</h4>
                                    <p class="text-sm leading-6 text-[#A0A0A8]">
                                        {{ $itemSummary ?: __('Ringkasan alat belum tersedia') }}
                                        @if ($itemCount > 0)
                                            <span class="text-[#7C7C84]">• {{ $itemCount }} {{ __('unit') }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5 text-[#E8E8EC]">
                                        {{ optional($order->rental_start_date)->translatedFormat('d M Y') }} — {{ optional($order->rental_end_date)->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5 text-[#E8E8EC]">
                                        {{ $durationDays }} {{ __('hari') }}
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

                            <div class="w-full xl:w-[280px] xl:shrink-0">
                                <div class="rounded-2xl border border-[#1A1A1E] bg-[#111113] p-4">
                                    <p class="text-xs font-medium text-[#A0A0A8]">{{ __('Total') }}</p>
                                    <p class="mt-1 text-2xl font-bold tracking-tight text-[#E8E8EC]">
                                        {{ $formatIdr($order->grand_total ?? $order->total_amount) }}
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a
                                            href="{{ route('account.orders.show', $order) }}"
                                            class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]"
                                        >
                                            {!! 'Detail & Ubah Jadwal' !!}
                                        </a>

                                        @if ($canPayOrder($order))
                                            <a
                                                href="{{ route('booking.pay', $order) }}"
                                                class="inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-3.5 py-2 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]"
                                            >
                                                {{ __('Bayar Sekarang') }}
                                            </a>
                                        @endif

                                        @if ($canRefreshStatus($order))
                                            <form method="POST" action="{{ route('payments.refresh-status', $order) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]"
                                                >
                                                    {{ __('Refresh Status') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($signedInvoiceUrl)
                                            <a
                                                href="{{ $signedInvoiceUrl }}"
                                                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]"
                                            >
                                                {{ __('Invoice') }}
                                            </a>
                                        @endif

                                        @if ($canCancelOrder($order))
                                            <form
                                                method="POST"
                                                action="{{ route('account.orders.cancel', $order) }}"
                                                onsubmit="return confirm('{{ __('Apakah Anda yakin ingin membatalkan pesanan ini?') }}');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl border border-rose-400/25 bg-rose-500/5 px-3.5 py-2 text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10"
                                                >
                                                    {{ __('Batalkan') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 bg-[#0A0A0B]/60 px-6 py-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-[#1A1A1E] bg-[#111113]">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#D4A843]"></span>
                        </div>
                        <h4 class="mt-4 text-lg font-semibold text-[#E8E8EC]">{{ __('Belum ada sewa aktif') }}</h4>
                        <p class="mt-2 text-sm leading-6 text-[#A0A0A8]">{{ __('Pesanan yang sedang berjalan akan muncul di sini.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="history-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6" style="animation-delay: 220ms">
            <div>
                <h3 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Riwayat Terbaru') }}</h3>
                <p class="mt-1 text-sm text-[#A0A0A8]">{{ __('Pesanan terbaru, pembayaran, dan akses detail order Anda.') }}</p>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentBookings as $order)
                    @php
                        $orderNumber = $order->order_number ?? ('ORD-' . $order->id);
                    @endphp
                    <article class="history-card-in rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/75 p-4" style="animation-delay: {{ min(($loop->index + 8) * 40, 340) }}ms">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="break-all text-sm font-semibold text-[#E8E8EC]">{{ $orderNumber }}</p>
                                <p class="mt-1 text-xs leading-5 text-[#A0A0A8]">
                                    {{ optional($order->created_at)->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone('payment', $order->status_pembayaran ?? Order::PAYMENT_PENDING) }}">
                                {{ $paymentLabel($order->status_pembayaran ?? Order::PAYMENT_PENDING) }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium text-[#A0A0A8]">{{ __('Total') }}</p>
                                <p class="mt-1 text-lg font-semibold text-[#E8E8EC]">
                                    {{ $formatIdr($order->grand_total ?? $order->total_amount) }}
                                </p>
                                <p class="mt-1 text-xs text-[#A0A0A8]">
                                    {{ $rentalLabel($order->status_pesanan ?? Order::STATUS_PENDING_PAYMENT) }}
                                </p>
                            </div>

                            <a
                                href="{{ route('account.orders.show', $order) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-2 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]"
                            >
                                {{ __('Detail') }}
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 bg-[#0A0A0B]/60 px-6 py-10 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-[#1A1A1E] bg-[#111113]">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#D4A843]"></span>
                        </div>
                        <h4 class="mt-4 text-lg font-semibold text-[#E8E8EC]">{{ __('Belum ada riwayat') }}</h4>
                        <p class="mt-2 text-sm leading-6 text-[#A0A0A8]">{{ __('Pesanan selesai atau transaksi terbaru akan tampil di sini.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @if (isset($orders) && method_exists($orders, 'links'))
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection

@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', __('ui.admin_orders.title'))
@section('page_title', __('ui.admin_orders.page_title'))

@push('head')
<style>
    .admin-orders-page {
        color: var(--admin-text);
    }

    .admin-orders-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-orders-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-orders-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-orders-card-header {
        background: var(--admin-surface-raised);
        border-color: var(--admin-border);
    }

    html[data-theme-resolved="light"] .admin-orders-card-header {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] .admin-orders-card-header {
        background: #151519 !important;
        border-color: #1A1A1E !important;
    }

    .admin-orders-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-orders-title {
        color: var(--admin-text);
    }

    .admin-orders-muted {
        color: var(--admin-muted);
    }

    .admin-orders-subtle {
        color: var(--admin-subtle);
    }

    .admin-orders-input {
        width: 100%;
        min-height: 3.25rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        outline: none;
    }

    .admin-orders-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] .admin-orders-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] .admin-orders-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-orders-inner {
        background: var(--admin-surface-raised);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
    }

    html[data-theme-resolved="light"] .admin-orders-inner {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] .admin-orders-inner {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }

    .admin-orders-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] .admin-orders-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] .admin-orders-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-orders-table tbody tr {
        border-color: var(--admin-border);
    }

    .admin-orders-table tbody tr:hover {
        background: var(--admin-surface-raised);
    }

    html[data-theme-resolved="light"] .admin-orders-table tbody tr:hover {
        background: #F8FAFC !important;
    }

    html[data-theme-resolved="dark"] .admin-orders-table tbody tr:hover {
        background: #151519 !important;
    }
</style>
@endpush

@section('content')
    @php
        $ordersCopy = __('ui.admin_orders');
    @endphp

    <div class="admin-orders-page space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header & Filters --}}
        <section class="admin-orders-card p-5 sm:p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="admin-orders-kicker">{{ $ordersCopy['kicker'] }}</p>
                    <h2 class="admin-orders-title mt-2 text-2xl font-black">
                        {{ $ordersCopy['heading'] }}
                    </h2>
                    <p class="admin-orders-muted mt-1 text-sm">
                        {{ $ordersCopy['subtitle'] }}
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_240px_auto]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ $ordersCopy['filters']['search_placeholder'] }}"
                    class="admin-orders-input"
                >

                <select name="status" class="admin-orders-input">
                    <option value="">{{ $ordersCopy['filters']['payment_status_all'] }}</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>{{ $ordersCopy['payment_status']['pending'] }}</option>
                    <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>{{ $ordersCopy['payment_status']['paid'] }}</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>{{ $ordersCopy['payment_status']['failed'] }}</option>
                    <option value="expired" {{ ($status ?? '') === 'expired' ? 'selected' : '' }}>{{ $ordersCopy['payment_status']['expired'] }}</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>{{ $ordersCopy['payment_status']['refunded'] }}</option>
                </select>

                <button class="admin-accent-bg inline-flex min-h-[3.25rem] items-center justify-center rounded-xl px-6 text-sm font-bold transition">
                    {{ $ordersCopy['filters']['apply'] }}
                </button>
            </form>
        </section>

        {{-- Archives & Logs --}}
        <section class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.75fr)]">
            <article class="admin-orders-card flex min-h-0 flex-col overflow-hidden p-0">
                <div class="admin-orders-card-header border-b px-5 py-4">
                    <p class="admin-orders-kicker">{{ $ordersCopy['archive']['kicker'] }}</p>
                    <h3 class="admin-orders-title mt-2 text-xl font-black">
                        {{ $ordersCopy['archive']['title'] }}
                    </h3>
                </div>

                <div class="scroll-panel max-h-[30rem] overflow-y-auto p-5">
                    <div class="grid gap-3 md:grid-cols-2">
                    @forelse (($monthlyRecaps ?? collect()) as $recap)
                        <article class="admin-orders-inner rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold admin-orders-title">{{ $recap['label'] }}</p>
                                    <p class="text-[11px] admin-orders-muted">{{ $recap['period_label'] }}</p>
                                </div>
                                <span class="status-chip status-chip-success">
                                    {{ str_replace(':count', $recap['orders_count'], $ordersCopy['archive']['orders_count']) }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="admin-orders-inner rounded-xl px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide admin-orders-muted">{{ $ordersCopy['archive']['unit'] }}</p>
                                    <p class="mt-1 text-sm font-semibold admin-orders-title">{{ $recap['units_total'] }}</p>
                                </div>
                                <div class="admin-orders-inner rounded-xl px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide admin-orders-muted">{{ $ordersCopy['archive']['total'] }}</p>
                                    <p class="mt-1 text-sm font-semibold admin-orders-title">{{ __('Rp') }} {{ number_format((int) $recap['revenue_total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @if (! empty($recap['latest_orders']))
                                <div class="scroll-panel mt-3 max-h-[8.75rem] space-y-2 overflow-y-auto border-t admin-border pt-3 pr-1">
                                    @foreach ($recap['latest_orders'] as $archivedOrder)
                                        <a href="{{ route('admin.orders.show', $archivedOrder['id']) }}" class="admin-orders-inner block rounded-xl px-3 py-2 transition hover:border-[var(--admin-accent-border)]">
                                            <p class="truncate text-xs font-semibold admin-accent-text">
                                                {{ $archivedOrder['archive_label'] }}
                                            </p>
                                            <p class="admin-orders-muted mt-1 truncate text-[11px]">{{ $archivedOrder['customer_name'] }}</p>
                                            <p class="admin-orders-subtle mt-1 truncate text-[11px]">{{ $archivedOrder['order_number'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed admin-border px-4 py-5 text-sm admin-orders-muted md:col-span-2">
                            {{ $ordersCopy['archive']['empty'] }}
                        </div>
                    @endforelse
                    </div>
                </div>
            </article>

            <article class="admin-orders-card flex min-h-0 flex-col overflow-hidden p-0">
                <div class="admin-orders-card-header border-b px-5 py-4">
                    <p class="admin-orders-kicker">{{ $ordersCopy['logs']['kicker'] }}</p>
                    <h3 class="admin-orders-title mt-2 text-xl font-black">
                        {{ $ordersCopy['logs']['title'] }}
                    </h3>
                </div>

                <div class="scroll-panel max-h-[30rem] space-y-3 overflow-y-auto p-5">
                    @forelse (($orderLogs ?? collect()) as $log)
                        <article class="admin-orders-inner rounded-xl px-3 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold admin-orders-title">{{ $log['order_number'] ?? $ordersCopy['kicker'] }}</p>
                                    <p class="mt-1 text-xs admin-orders-muted">{{ $log['summary'] }}</p>
                                </div>
                                <span class="admin-orders-subtle shrink-0 text-[11px]">{{ optional($log['created_at'])->format('d M H:i') }}</span>
                            </div>
                            <p class="mt-2 text-[11px] admin-orders-subtle">{{ $log['admin_name'] ?: $ordersCopy['logs']['system'] }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed admin-border px-4 py-5 text-sm admin-orders-muted">
                            {{ $ordersCopy['logs']['empty'] }}
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        {{-- Main Orders Table --}}
        <section class="admin-orders-card overflow-hidden p-0">
            <div class="admin-orders-card-header flex flex-col gap-1 border-b px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="admin-orders-title text-xl font-black">
                        {{ $ordersCopy['table']['title'] }}
                    </h3>
                    <p class="admin-orders-muted text-sm">
                        {{ $ordersCopy['table']['subtitle'] }}
                    </p>
                </div>
            </div>

            <div class="scroll-panel max-h-[34rem] overflow-auto">
                <table class="admin-orders-table min-w-[920px] w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-left">{{ $ordersCopy['table']['order'] }}</th>
                            <th class="px-5 py-3 text-left">{{ $ordersCopy['table']['user'] }}</th>
                            <th class="px-5 py-3 text-left">{{ $ordersCopy['table']['total'] }}</th>
                            <th class="px-5 py-3 text-left">{{ $ordersCopy['table']['payment_status'] }}</th>
                            <th class="px-5 py-3 text-left">{{ $ordersCopy['table']['order_status'] }}</th>
                            <th class="px-5 py-3 text-right">{{ $ordersCopy['table']['action'] }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y admin-border">
                        @forelse ($orders as $order)
                            @php
                                $paymentBadge = match($order->status_pembayaran) {
                                    'paid' => 'status-chip-success',
                                    'failed' => 'status-chip-danger',
                                    'expired' => 'status-chip-muted',
                                    'refunded' => 'status-chip-info',
                                    default => 'status-chip-warning',
                                };
                                $paymentStatusLabel = match($order->status_pembayaran) {
                                    'paid' => $ordersCopy['payment_status']['paid'],
                                    'failed' => $ordersCopy['payment_status']['failed'],
                                    'expired' => $ordersCopy['payment_status']['expired'],
                                    'refunded' => $ordersCopy['payment_status']['refunded'],
                                    default => $ordersCopy['payment_status']['pending'],
                                };
                                $orderStatusLabel = $ordersCopy['order_status'][$order->status_pesanan] ?? strtoupper((string) ($order->status_pesanan ?? '-'));
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold admin-orders-title">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs admin-orders-muted">{{ $order->created_at?->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold admin-orders-title">{{ $order->user?->name ?? '-' }}</p>
                                    <p class="text-sm admin-orders-muted">{{ $order->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold admin-orders-title">{{ __('Rp') }} {{ number_format((int) ($order->grand_total ?? $order->total_amount), 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-chip {{ $paymentBadge }}">
                                        {{ mb_strtoupper($paymentStatusLabel) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 admin-orders-muted">{{ $orderStatusLabel }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="admin-secondary-button inline-flex rounded-xl px-3 py-1.5 text-xs font-semibold transition">
                                        {{ $ordersCopy['table']['detail'] }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm admin-orders-muted">
                                    {{ $ordersCopy['table']['empty'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $orders->links() }}
    </div>
@endsection

@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', __('Daftar Pesanan'))
@section('page_title', __('Daftar Pesanan'))

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="manake-card-soft px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="manake-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="manake-kicker">{{ __('Pesanan') }}</p>
                    <h2 class="manake-heading mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ __('Daftar Pesanan') }}</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[1fr,180px,auto]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Cari pesanan / email pengguna...') }}"
                    class="manake-input"
                >
                <select name="status" class="manake-input">
                    <option value="">{{ __('Semua Status Pembayaran') }}</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu') }}</option>
                    <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>{{ __('Lunas') }}</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>{{ __('Gagal') }}</option>
                    <option value="expired" {{ ($status ?? '') === 'expired' ? 'selected' : '' }}>{{ __('Kedaluwarsa') }}</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>{{ __('Refund') }}</option>
                </select>
                <button class="btn-primary">{{ __('Terapkan') }}</button>
            </form>
        </section>

        <section class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
            <article class="manake-card flex min-h-0 flex-col overflow-hidden p-0">
                <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="px-5 pt-5">
                        <p class="manake-kicker">{{ __('Arsip') }}</p>
                        <h3 class="manake-heading mt-2 text-xl font-black text-slate-950 dark:text-white">{{ __('Arsip Bulanan') }}</h3>
                    </div>
                </div>
            </div>

                <div class="scroll-panel mt-4 h-[29rem] overflow-y-auto pr-1">
                    <div class="grid gap-3 md:grid-cols-2">
                    @forelse (($monthlyRecaps ?? collect()) as $recap)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $recap['label'] }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $recap['period_label'] }}</p>
                                </div>
                                <span class="status-chip status-chip-success">{{ $recap['orders_count'] }} {{ __('pesanan') }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-950">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('Unit') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $recap['units_total'] }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 dark:border-slate-800 dark:bg-slate-950">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ __('Total') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Rp') }} {{ number_format((int) $recap['revenue_total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @if (! empty($recap['latest_orders']))
                                <div class="scroll-panel mt-3 max-h-[8.75rem] space-y-2 overflow-y-auto border-t border-slate-200 pt-3 pr-1 dark:border-slate-800">
                                    @foreach ($recap['latest_orders'] as $archivedOrder)
                                        <a href="{{ route('admin.orders.show', $archivedOrder['id']) }}" class="block rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-blue-200 dark:border-slate-800 dark:bg-slate-950">
                                            <p class="truncate text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                {{ $archivedOrder['archive_label'] }}
                                            </p>
                                            <p class="mt-1 truncate text-[11px] text-slate-600 dark:text-slate-400">{{ $archivedOrder['customer_name'] }}</p>
                                            <p class="mt-1 truncate text-[11px] text-slate-500 dark:text-slate-500">{{ $archivedOrder['order_number'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400 md:col-span-2">
                            {{ __('Belum ada arsip bulanan.') }}
                        </div>
                    @endforelse
                    </div>
                </div>
            </article>

            <article class="manake-card flex min-h-0 flex-col overflow-hidden p-0">
                <div>
                    <div class="px-5 pt-5">
                        <p class="manake-kicker">{{ __('Log') }}</p>
                        <h3 class="manake-heading mt-2 text-xl font-black text-slate-950 dark:text-white">{{ __('Log Pesanan') }}</h3>
                    </div>
                </div>

                <div class="scroll-panel mt-4 h-[29rem] space-y-3 overflow-y-auto pr-1">
                    @forelse (($orderLogs ?? collect()) as $log)
                        <article class="rounded-xl border border-slate-200 px-3 py-3 dark:border-slate-800 dark:bg-slate-950">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $log['order_number'] ?? __('Pesanan') }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $log['summary'] }}</p>
                                </div>
                                <span class="shrink-0 text-[11px] text-slate-400 dark:text-slate-500">{{ optional($log['created_at'])->format('d M H:i') }}</span>
                            </div>
                            <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">{{ $log['admin_name'] ?: __('Sistem') }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            {{ __('Belum ada log pesanan.') }}
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="manake-card overflow-hidden p-0">
            <div class="scroll-panel max-h-[34rem] overflow-auto">
                <table class="min-w-[860px] w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900 dark:text-slate-300">
                        <tr>
                            <th class="px-5 py-3">{{ __('Pesanan') }}</th>
                            <th class="px-5 py-3">{{ __('Pengguna') }}</th>
                            <th class="px-5 py-3">{{ __('Total') }}</th>
                            <th class="px-5 py-3">{{ __('Status Bayar') }}</th>
                            <th class="px-5 py-3">{{ __('Status Pesanan') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
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
                                    'paid' => __('LUNAS'),
                                    'failed' => __('GAGAL'),
                                    'expired' => __('KEDALUWARSA'),
                                    'refunded' => __('REFUND'),
                                    default => __('MENUNGGU'),
                                };
                                $orderStatusLabel = match($order->status_pesanan) {
                                    'menunggu_pembayaran' => __('Menunggu Bayar'),
                                    'diproses' => __('Diproses'),
                                    'lunas' => __('Siap Diambil'),
                                    'barang_diambil' => __('Barang Diambil'),
                                    'barang_kembali' => __('Barang Kembali'),
                                    'barang_rusak' => __('Barang Rusak'),
                                    'expired' => __('Kedaluwarsa'),
                                    'selesai' => __('Selesai'),
                                    'dibatalkan' => __('Dibatalkan'),
                                    'refund' => __('Pengembalian Dana'),
                                    default => strtoupper((string) ($order->status_pesanan ?? '-')),
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/60">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $order->created_at?->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $order->user?->name ?? '-' }}</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $order->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-100">{{ __('Rp') }} {{ number_format((int) ($order->grand_total ?? $order->total_amount), 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-chip {{ $paymentBadge }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $orderStatusLabel }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:text-slate-300 dark:hover:border-blue-500/40 dark:hover:text-blue-300">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada pesanan.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $orders->links() }}
    </div>
@endsection

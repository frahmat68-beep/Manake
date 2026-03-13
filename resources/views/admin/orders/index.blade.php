@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', __('Daftar Pesanan'))
@section('page_title', __('Daftar Pesanan'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-blue-700">{{ __('Daftar Pesanan') }}</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[1fr,180px,auto]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Cari pesanan / email pengguna...') }}"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                >
                <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                    <option value="">{{ __('Semua Status Pembayaran') }}</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu') }}</option>
                    <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>{{ __('Lunas') }}</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>{{ __('Gagal') }}</option>
                    <option value="expired" {{ ($status ?? '') === 'expired' ? 'selected' : '' }}>{{ __('Kedaluwarsa') }}</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>{{ __('Refund') }}</option>
                </select>
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">{{ __('Terapkan') }}</button>
            </form>
        </section>

        <section class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
            <article class="flex h-[34rem] min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">{{ __('Arsip Bulanan') }}</h3>
                </div>
            </div>

                <div class="scroll-panel mt-4 min-h-0 flex-1 overflow-y-auto pr-1">
                    <div class="grid gap-3 md:grid-cols-2">
                    @forelse (($monthlyRecaps ?? collect()) as $recap)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $recap['label'] }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $recap['period_label'] }}</p>
                                </div>
                                <span class="status-chip status-chip-success">{{ $recap['orders_count'] }} {{ __('pesanan') }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Unit') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $recap['units_total'] }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">{{ __('Total') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ __('Rp') }} {{ number_format((int) $recap['revenue_total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @if (! empty($recap['latest_orders']))
                                <div class="scroll-panel mt-3 max-h-[8.75rem] space-y-2 overflow-y-auto border-t border-slate-200 pt-3 pr-1">
                                    @foreach ($recap['latest_orders'] as $archivedOrder)
                                        <a href="{{ route('admin.orders.show', $archivedOrder['id']) }}" class="block rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-blue-200">
                                            <p class="truncate text-xs font-semibold text-blue-600 hover:text-blue-700">
                                                {{ $archivedOrder['archive_label'] }}
                                            </p>
                                            <p class="mt-1 truncate text-[11px] text-slate-600">{{ $archivedOrder['customer_name'] }}</p>
                                            <p class="mt-1 truncate text-[11px] text-slate-500">{{ $archivedOrder['order_number'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500 md:col-span-2">
                            {{ __('Belum ada arsip bulanan.') }}
                        </div>
                    @endforelse
                    </div>
                </div>
            </article>

            <article class="flex h-[34rem] min-h-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">{{ __('Log Pesanan') }}</h3>
                </div>

                <div class="scroll-panel mt-4 min-h-0 max-h-[29rem] flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse (($orderLogs ?? collect()) as $log)
                        <article class="rounded-xl border border-slate-200 px-3 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $log['order_number'] ?? __('Pesanan') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $log['summary'] }}</p>
                                </div>
                                <span class="shrink-0 text-[11px] text-slate-400">{{ optional($log['created_at'])->format('d M H:i') }}</span>
                            </div>
                            <p class="mt-2 text-[11px] text-slate-400">{{ $log['admin_name'] ?: __('Sistem') }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-5 text-sm text-slate-500">
                            {{ __('Belum ada log pesanan.') }}
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="scroll-panel max-h-[34rem] overflow-auto">
                <table class="min-w-[860px] w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
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
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->created_at?->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $order->user?->name ?? '-' }}</p>
                                    <p class="text-sm text-slate-600">{{ $order->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ __('Rp') }} {{ number_format((int) ($order->grand_total ?? $order->total_amount), 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-chip {{ $paymentBadge }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $orderStatusLabel }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('Belum ada pesanan.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $orders->links() }}
    </div>
@endsection

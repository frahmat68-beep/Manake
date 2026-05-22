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

        <section class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="manake-kicker">{{ __('Pesanan') }}</p>
                    <h2 class="manake-heading mt-2 text-2xl font-black text-[#E8E8EC]">{{ __('Daftar Pesanan') }}</h2>
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
            <article class="flex min-h-0 flex-col overflow-hidden rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-0 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                <div class="px-5 pt-5">
                    <p class="manake-kicker">{{ __('Arsip') }}</p>
                    <h3 class="manake-heading mt-2 text-xl font-black text-[#E8E8EC]">{{ __('Arsip Bulanan') }}</h3>
                </div>

                <div class="scroll-panel mt-4 h-[29rem] overflow-y-auto pr-1">
                    <div class="grid gap-3 md:grid-cols-2">
                    @forelse (($monthlyRecaps ?? collect()) as $recap)
                        <article class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $recap['label'] }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $recap['period_label'] }}</p>
                                </div>
                                <span class="status-chip status-chip-success">{{ $recap['orders_count'] }} {{ __('pesanan') }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-xl border border-white/10 bg-[#0A0A0B] px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-[#A0A0A8]">{{ __('Unit') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-[#E8E8EC]">{{ $recap['units_total'] }}</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-[#0A0A0B] px-3 py-2">
                                    <p class="text-[11px] uppercase tracking-wide text-[#A0A0A8]">{{ __('Total') }}</p>
                                    <p class="mt-1 text-sm font-semibold text-[#E8E8EC]">{{ __('Rp') }} {{ number_format((int) $recap['revenue_total'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @if (! empty($recap['latest_orders']))
                                <div class="scroll-panel mt-3 max-h-[8.75rem] space-y-2 overflow-y-auto border-t border-white/10 pt-3 pr-1">
                                    @foreach ($recap['latest_orders'] as $archivedOrder)
                                        <a href="{{ route('admin.orders.show', $archivedOrder['id']) }}" class="block rounded-xl border border-white/10 bg-[#0A0A0B] px-3 py-2 transition hover:border-[#D4A843]/40">
                                            <p class="truncate text-xs font-semibold text-[#D4A843]">
                                                {{ $archivedOrder['archive_label'] }}
                                            </p>
                                            <p class="mt-1 truncate text-[11px] text-[#A0A0A8]">{{ $archivedOrder['customer_name'] }}</p>
                                            <p class="mt-1 truncate text-[11px] text-[#66666C]">{{ $archivedOrder['order_number'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/10 px-4 py-5 text-sm text-[#A0A0A8] md:col-span-2">
                            {{ __('Belum ada arsip bulanan.') }}
                        </div>
                    @endforelse
                    </div>
                </div>
            </article>

            <article class="flex min-h-0 flex-col overflow-hidden rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-0 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                <div class="px-5 pt-5">
                    <p class="manake-kicker">{{ __('Log') }}</p>
                    <h3 class="manake-heading mt-2 text-xl font-black text-[#E8E8EC]">{{ __('Log Pesanan') }}</h3>
                </div>

                <div class="scroll-panel mt-4 h-[29rem] space-y-3 overflow-y-auto pr-1">
                    @forelse (($orderLogs ?? collect()) as $log)
                        <article class="rounded-xl border border-white/10 bg-[#0A0A0B] px-3 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-[#E8E8EC]">{{ $log['order_number'] ?? __('Pesanan') }}</p>
                                    <p class="mt-1 text-xs text-[#A0A0A8]">{{ $log['summary'] }}</p>
                                </div>
                                <span class="shrink-0 text-[11px] text-[#66666C]">{{ optional($log['created_at'])->format('d M H:i') }}</span>
                            </div>
                            <p class="mt-2 text-[11px] text-[#66666C]">{{ $log['admin_name'] ?: __('Sistem') }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/10 px-4 py-5 text-sm text-[#A0A0A8]">
                            {{ __('Belum ada log pesanan.') }}
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-0 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
            <div class="scroll-panel max-h-[34rem] overflow-auto">
                <table class="min-w-[860px] w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-[#0A0A0B] text-left text-xs uppercase tracking-wide text-[#A0A0A8]">
                        <tr>
                            <th class="px-5 py-3">{{ __('Pesanan') }}</th>
                            <th class="px-5 py-3">{{ __('Pengguna') }}</th>
                            <th class="px-5 py-3">{{ __('Total') }}</th>
                            <th class="px-5 py-3">{{ __('Status Bayar') }}</th>
                            <th class="px-5 py-3">{{ __('Status Pesanan') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
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
                                    <p class="font-semibold text-[#E8E8EC]">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs text-[#A0A0A8]">{{ $order->created_at?->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#E8E8EC]">{{ $order->user?->name ?? '-' }}</p>
                                    <p class="text-sm text-[#A0A0A8]">{{ $order->user?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#E8E8EC]">{{ __('Rp') }} {{ number_format((int) ($order->grand_total ?? $order->total_amount), 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-chip {{ $paymentBadge }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-[#A0A0A8]">{{ $orderStatusLabel }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-white/10 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-[#A0A0A8]">{{ __('Belum ada pesanan.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $orders->links() }}
    </div>
@endsection

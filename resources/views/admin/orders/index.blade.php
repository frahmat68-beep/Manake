@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', 'Daftar Pesanan')
@section('page_title', 'Daftar Pesanan')

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
                    <h2 class="text-lg font-semibold text-blue-700">Daftar Pesanan</h2>
                    <p class="text-xs text-slate-500">Pantau transaksi, pembayaran, dan status pesanan.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-[1fr,180px,auto]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari pesanan / email pengguna..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                >
                <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                    <option value="">Semua Status Pembayaran</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="paid" {{ ($status ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Terapkan</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Pesanan</th>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status Bayar</th>
                            <th class="px-5 py-3">Status Pesanan</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            @php
                                $paymentBadge = match($order->status_pembayaran) {
                                    'paid' => 'status-chip-success',
                                    'failed' => 'status-chip-danger',
                                    default => 'status-chip-warning',
                                };
                                $paymentStatusLabel = match($order->status_pembayaran) {
                                    'paid' => 'LUNAS',
                                    'failed' => 'GAGAL',
                                    default => 'MENUNGGU',
                                };
                                $orderStatusLabel = match($order->status_pesanan) {
                                    'menunggu_pembayaran' => 'Menunggu Bayar',
                                    'diproses' => 'Diproses',
                                    'lunas' => 'Siap Diambil',
                                    'barang_diambil' => 'Barang Diambil',
                                    'barang_kembali' => 'Barang Kembali',
                                    'barang_rusak' => 'Barang Rusak',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan',
                                    'refund' => 'Pengembalian Dana',
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
                                <td class="px-5 py-4 font-semibold text-slate-800">Rp {{ number_format((int) ($order->grand_total ?? $order->total_amount), 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-chip {{ $paymentBadge }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $orderStatusLabel }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $orders->links() }}
    </div>
@endsection

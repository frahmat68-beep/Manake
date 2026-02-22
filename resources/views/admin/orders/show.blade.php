@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', 'Detail Pesanan')
@section('page_title', 'Detail Pesanan')

@section('content')
    @php
        $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');

        $statusLabel = function (?string $status) {
            return match ($status) {
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'diproses' => 'Diproses',
                'lunas' => 'Siap Diambil',
                'barang_diambil' => 'Barang Diambil',
                'barang_kembali' => 'Barang Dikembalikan',
                'barang_rusak' => 'Barang Rusak',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                'refund' => 'Pengembalian Dana',
                default => strtoupper((string) $status),
            };
        };
        $paymentLabel = function (?string $status) {
            return match ((string) $status) {
                'paid' => 'Lunas',
                'failed' => 'Gagal',
                'expired' => 'Kedaluwarsa',
                default => 'Menunggu',
            };
        };
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Pesanan</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $order->order_number ?? ('ORD-' . $order->id) }}</h2>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600">← Kembali ke Daftar Pesanan</a>
        </div>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr,0.8fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Item Pesanan</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($order->items as $item)
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item->equipment?->name ?? 'Alat' }}</p>
                                    <p class="text-xs text-slate-500">Qty {{ $item->qty }} x {{ $formatIdr($item->price) }}</p>
                                </div>
                                <p class="font-semibold text-slate-800">{{ $formatIdr($item->subtotal) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Tidak ada item di pesanan ini.</p>
                    @endforelse
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Pembayaran:</span> {{ $paymentLabel($order->status_pembayaran) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">Status Sewa:</span> {{ $statusLabel($order->status_pesanan) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Subtotal:</span> {{ $formatIdr($order->total_amount) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">Biaya Tambahan:</span> {{ $formatIdr($order->additional_fee ?? 0) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">Total Akhir:</span> {{ $formatIdr($order->grand_total) }}</p>
                    </div>
                </div>
            </article>

            <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Kontrol Status</h3>
                <div class="space-y-2 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-800">Pengguna:</span> {{ $order->user?->name ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">Email:</span> {{ $order->user?->email ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">Periode:</span> {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}</p>
                    @if ($order->picked_up_at)
                        <p><span class="font-semibold text-slate-800">Diambil:</span> {{ $order->picked_up_at->format('d M Y H:i') }}</p>
                    @endif
                    @if ($order->returned_at)
                        <p><span class="font-semibold text-slate-800">Dikembalikan:</span> {{ $order->returned_at->format('d M Y H:i') }}</p>
                    @endif
                    @if ($order->damaged_at)
                        <p><span class="font-semibold text-slate-800">Rusak Dilaporkan:</span> {{ $order->damaged_at->format('d M Y H:i') }}</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Status Pembayaran</label>
                        <select name="status_pembayaran" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach (['pending', 'paid', 'failed'] as $paymentStatus)
                                @php
                                    $paymentStatusText = match ($paymentStatus) {
                                        'paid' => 'Lunas',
                                        'failed' => 'Gagal',
                                        default => 'Menunggu',
                                    };
                                @endphp
                                <option value="{{ $paymentStatus }}" {{ $order->status_pembayaran === $paymentStatus ? 'selected' : '' }}>
                                    {{ $paymentStatusText }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Status Pesanan</label>
                        <select name="status_pesanan" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach ($statusPesananOptions as $orderStatus)
                                <option value="{{ $orderStatus }}" {{ $order->status_pesanan === $orderStatus ? 'selected' : '' }}>
                                    {{ $statusLabel($orderStatus) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Biaya Tambahan</label>
                        <input
                            type="number"
                            min="0"
                            step="1000"
                            name="additional_fee"
                            value="{{ old('additional_fee', (int) ($order->additional_fee ?? 0)) }}"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Keterangan Biaya Tambahan</label>
                        <input
                            type="text"
                            name="additional_fee_note"
                            value="{{ old('additional_fee_note', $order->additional_fee_note) }}"
                            placeholder="Contoh: Denda keterlambatan 1 hari"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Catatan Admin ke Pengguna</label>
                        <textarea
                            name="admin_note"
                            rows="3"
                            placeholder="Contoh: Barang ditemukan rusak pada bagian tombol record"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >{{ old('admin_note', $order->admin_note) }}</textarea>
                    </div>

                    <p class="text-xs text-slate-500">Setiap perubahan status/biaya/catatan otomatis dikirim ke notifikasi pengguna.</p>

                    <button class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        Simpan & Kirim Notifikasi
                    </button>
                </form>
            </aside>
        </section>
    </div>
@endsection

@extends('layouts.admin', ['activePage' => 'dashboard'])

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Operasional')

@section('content')
    @php
        $statusBadge = fn (?string $status) => match ($status) {
            'lunas' => ['label' => 'Siap Diambil', 'class' => 'status-chip-info'],
            'barang_diambil' => ['label' => 'Sedang Disewa', 'class' => 'status-chip-warning'],
            'barang_kembali' => ['label' => 'Sudah Kembali', 'class' => 'status-chip-success'],
            'barang_rusak' => ['label' => 'Barang Rusak', 'class' => 'status-chip-danger'],
            default => ['label' => strtoupper((string) $status), 'class' => 'status-chip-muted'],
        };
        $rentalCalendar = $rentalCalendar ?? [];
        $calendarDays = collect($rentalCalendar['days'] ?? []);
        $calendarBaseQuery = request()->except(['calendar_month', 'page']);
        $financialSummary = $financialSummary ?? [];
        $formatIdr = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
        $isPaginator = $operationalOrders instanceof \Illuminate\Pagination\AbstractPaginator;
        $ordersCollection = $isPaginator ? $operationalOrders->getCollection() : collect($operationalOrders ?? []);
        $actionableCount = $ordersCollection->filter(fn ($order) => in_array((string) ($order->status_pesanan ?? ''), ['lunas', 'barang_diambil'], true))->count();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-[repeat(4,minmax(0,1fr))_minmax(0,1.2fr)]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Siap Diambil</p>
                <p class="mt-2 text-3xl font-semibold text-blue-600">{{ (int) ($summary['ready_pickup'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sedang Disewa</p>
                <p class="mt-2 text-3xl font-semibold text-amber-600">{{ (int) ($summary['on_rent'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sudah Kembali</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ (int) ($summary['returned'] ?? 0) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kasus Rusak</p>
                <p class="mt-2 text-3xl font-semibold text-rose-600">{{ (int) ($summary['damaged'] ?? 0) }}</p>
            </article>
            <article class="col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Prioritas Hari Ini</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $actionableCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Pesanan yang butuh konfirmasi diambil/dikembalikan.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                        Buka Semua Pesanan
                    </a>
                    <a href="{{ route('admin.equipments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                        Cek Stok Alat
                    </a>
                    <a href="{{ route('availability.board') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                        Kalender Ketersediaan
                    </a>
                </div>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Uang Masuk</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $formatIdr($financialSummary['cash_in'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-slate-500">Total pembayaran sukses (termasuk fee tambahan).</p>
            </article>
            <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Pendapatan Sewa</p>
                <p class="mt-2 text-2xl font-semibold text-blue-700">{{ $formatIdr($financialSummary['revenue'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-blue-600">Akumulasi subtotal pesanan lunas.</p>
            </article>
            <article class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Pajak Terkumpul</p>
                <p class="mt-2 text-2xl font-semibold text-amber-700">{{ $formatIdr($financialSummary['tax'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-amber-700">Estimasi PPN 11% dari pendapatan sewa.</p>
            </article>
            <article class="rounded-2xl border border-rose-100 bg-rose-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-500">Fee Kerusakan</p>
                <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $formatIdr($financialSummary['damage_fee'] ?? 0) }}</p>
                <p class="mt-1 text-xs text-rose-600">Biaya tambahan yang sudah berhasil dibayar.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-700">Pesanan Perlu Tindakan</h2>
                        <p class="text-xs text-slate-500">Fokus ke aksi inti: konfirmasi diambil, dikembalikan, atau rusak.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                        Kelola di halaman Pesanan →
                    </a>
                </div>

                @if ($ordersCollection->isEmpty())
                    <div class="px-5 py-8 text-sm text-slate-500">
                        Tidak ada pesanan operasional untuk ditindaklanjuti saat ini.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
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
                            <article class="px-5 py-4">
                                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-base font-semibold text-slate-900">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                            <span class="status-chip {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ $order->user?->name ?? '-' }} •
                                            {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}
                                        </p>
                                        @if ($itemsLabel !== '')
                                            <p class="mt-1 text-xs text-slate-500">
                                                Alat: {{ $itemsLabel }}@if($order->items->count() > 2) +{{ $order->items->count() - 2 }} lainnya @endif
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3 xl:max-w-[560px]">
                                        @if ($isReadyPickup && $canConfirmPickupNow)
                                            <form method="POST" action="{{ route('admin.dashboard.orders.operational-status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_diambil">
                                                <button class="w-full rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                    Konfirmasi Diambil
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center text-xs font-semibold text-slate-400">
                                                @if ($isOnRent || $isClosed)
                                                    Sudah Diambil
                                                @else
                                                    Menunggu Jadwal
                                                @endif
                                            </div>
                                        @endif

                                        @if ($isOnRent)
                                            <form method="POST" action="{{ route('admin.dashboard.orders.operational-status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_kembali">
                                                <button class="w-full rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                    Konfirmasi Kembali
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center text-xs font-semibold text-slate-400">
                                                @if ($order->status_pesanan === 'barang_kembali')
                                                    Sudah Kembali
                                                @elseif ($order->status_pesanan === 'barang_rusak')
                                                    Ditandai Rusak
                                                @else
                                                    Menunggu Diambil
                                                @endif
                                            </div>
                                        @endif

                                        @if ($isOnRent)
                                            <form method="POST" action="{{ route('admin.dashboard.orders.operational-status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_rusak">
                                                <button class="w-full rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                                    Tandai Rusak
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center text-xs font-semibold text-slate-400">
                                                @if ($order->status_pesanan === 'barang_rusak')
                                                    Sudah Ditandai
                                                @else
                                                    Menunggu Diambil
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                                        Detail Pesanan
                                    </a>
                                    @if ($isReadyPickup && ! $canConfirmPickupNow && $pickupOpenAt)
                                        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-700">
                                            Tombol "Diambil" aktif mulai {{ $pickupOpenAt->format('d M Y') }} (H-1).
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="space-y-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-blue-700">Alur Operasional Singkat</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li>1. Status <span class="font-semibold">Siap Diambil</span> bisa dikonfirmasi mulai H-1.</li>
                        <li>2. Setelah diambil, ubah ke <span class="font-semibold">Konfirmasi Kembali</span> saat unit kembali.</li>
                        <li>3. Gunakan <span class="font-semibold">Tandai Rusak</span> jika ada kerusakan saat pengembalian.</li>
                    </ul>
                </section>

                <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary class="cursor-pointer text-base font-semibold text-blue-700">
                        Kalender Unit Disewa (Opsional)
                    </summary>
                    <div class="mt-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-2 py-1.5">
                                <a
                                    href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['previous_month'] ?? now()->subMonth()->format('Y-m')])) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                    aria-label="Bulan sebelumnya"
                                >
                                    ←
                                </a>
                                <span class="min-w-[9rem] text-center text-sm font-semibold text-slate-700">{{ $rentalCalendar['month_label'] ?? now()->translatedFormat('F Y') }}</span>
                                <a
                                    href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['next_month'] ?? now()->addMonth()->format('Y-m')])) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                    aria-label="Bulan berikutnya"
                                >
                                    →
                                </a>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">Unit-hari: {{ (int) ($rentalCalendar['total_unit_days'] ?? 0) }}</span>
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-700">Puncak: {{ (int) ($rentalCalendar['max_daily_units'] ?? 0) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 -mx-1 overflow-x-auto px-1 pb-1 sm:mx-0 sm:overflow-visible sm:px-0">
                            <div class="min-w-[640px]">
                                <div class="grid grid-cols-7 gap-2">
                                    @foreach (['SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB', 'MIN'] as $weekday)
                                        <p class="text-center text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ $weekday }}</p>
                                    @endforeach
                                </div>
                                <div class="mt-2 grid grid-cols-7 gap-2">
                                    @foreach ($calendarDays as $day)
                                        @php
                                            $hasRental = (int) ($day['total_qty'] ?? 0) > 0;
                                        @endphp
                                        <div class="rounded-xl border px-2 py-2 {{ ($day['in_month'] ?? false) ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-50 text-slate-300' }}">
                                            <p class="text-xs font-semibold">{{ $day['day'] }}</p>
                                            <p class="mt-1 text-xs {{ $hasRental ? 'font-semibold text-blue-600' : 'text-slate-400' }}">
                                                {{ $hasRental ? ($day['total_qty'] . ' unit') : '-' }}
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
            <div class="border-t border-slate-200 pt-4">
                {{ $operationalOrders->links() }}
            </div>
        @endif
    </div>
@endsection

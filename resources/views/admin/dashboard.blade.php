@extends('layouts.admin', ['activePage' => 'dashboard'])

@section('title', __('Dashboard Admin'))
@section('page_title', __('Dashboard Operasional'))

@section('content')
    @php
        $statusBadge = fn (?string $status) => match ($status) {
            'lunas' => ['label' => __('Siap Diambil'), 'class' => 'status-chip-info'],
            'barang_diambil' => ['label' => __('Sedang Disewa'), 'class' => 'status-chip-warning'],
            'barang_kembali' => ['label' => __('Sudah Kembali'), 'class' => 'status-chip-success'],
            'barang_rusak' => ['label' => __('Barang Rusak'), 'class' => 'status-chip-danger'],
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

    <div class="space-y-6">
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

        <section class="grid grid-cols-2 gap-4 xl:grid-cols-[repeat(4,minmax(0,1fr))_minmax(0,1.2fr)]">
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.85)]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Siap Diambil') }}</p>
                <p class="mt-3 text-3xl font-black text-sky-300">{{ (int) ($summary['ready_pickup'] ?? 0) }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.85)]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Sedang Disewa') }}</p>
                <p class="mt-3 text-3xl font-black text-amber-300">{{ (int) ($summary['on_rent'] ?? 0) }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.85)]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Sudah Kembali') }}</p>
                <p class="mt-3 text-3xl font-black text-emerald-300">{{ (int) ($summary['returned'] ?? 0) }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.85)]">
                <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Kasus Rusak') }}</p>
                <p class="mt-3 text-3xl font-black text-rose-300">{{ (int) ($summary['damaged'] ?? 0) }}</p>
            </article>
            <article class="col-span-2 rounded-[1.35rem] border border-[#1A1A1E] bg-gradient-to-br from-[#111113] via-[#0A0A0B] to-[#111113] p-5 shadow-[0_18px_50px_-35px_rgba(0,0,0,0.9)] lg:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#D4A843]">{{ __('Prioritas Hari Ini') }}</p>
                <p class="mt-3 text-3xl font-black text-[#E8E8EC]">{{ $actionableCount }}</p>
                <p class="mt-1 text-xs leading-6 text-[#A0A0A8]">{{ __('Pesanan yang butuh konfirmasi diambil/dikembalikan.') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-3 py-2 text-xs font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                        {{ __('Buka Semua Pesanan') }}
                    </a>
                    <a href="{{ route('admin.equipments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                        {{ __('Cek Stok Alat') }}
                    </a>
                    <a href="{{ route('availability.board') }}" class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                        {{ __('Kalender Ketersediaan') }}
                    </a>
                </div>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Uang Masuk') }}</p>
                <p class="mt-3 text-2xl font-black text-[#E8E8EC]">{{ $formatIdr($financialSummary['cash_in'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-6 text-[#A0A0A8]">{{ __('Total pembayaran sukses (termasuk fee tambahan).') }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-300">{{ __('Pendapatan Sewa') }}</p>
                <p class="mt-3 text-2xl font-black text-[#E8E8EC]">{{ $formatIdr($financialSummary['revenue'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-6 text-[#A0A0A8]">{{ __('Akumulasi subtotal pesanan lunas.') }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-300">{{ __('Pajak Terkumpul') }}</p>
                <p class="mt-3 text-2xl font-black text-[#E8E8EC]">{{ $formatIdr($financialSummary['tax'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-6 text-[#A0A0A8]">{{ __('Estimasi PPN 11% dari pendapatan sewa.') }}</p>
            </article>
            <article class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-rose-300">{{ __('Fee Kerusakan') }}</p>
                <p class="mt-3 text-2xl font-black text-[#E8E8EC]">{{ $formatIdr($financialSummary['damage_fee'] ?? 0) }}</p>
                <p class="mt-1 text-xs leading-6 text-[#A0A0A8]">{{ __('Biaya tambahan yang sudah berhasil dibayar.') }}</p>
            </article>
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
            <section class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[#E8E8EC]">{{ __('Pesanan Perlu Tindakan') }}</h2>
                        <p class="text-xs text-[#A0A0A8]">{{ __('Daftar pesanan aktif yang membutuhkan konfirmasi operasional.') }}</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-[#D4A843] hover:text-[#e0ba5d]">
                        {{ __('Kelola di halaman Pesanan →') }}
                    </a>
                </div>

                @if ($ordersCollection->isEmpty())
                    <div class="px-5 py-8 text-sm text-[#A0A0A8]">
                        {{ __('Tidak ada pesanan operasional untuk ditindaklanjuti saat ini.') }}
                    </div>
                @else
                    <div class="divide-y divide-white/10">
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
                                        <p class="mt-1 text-sm text-[#A0A0A8]">
                                            {{ $order->user?->name ?? '-' }} •
                                            {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}
                                        </p>
                                        @if ($itemsLabel !== '')
                                            <p class="mt-1 text-xs text-[#66666C]">
                                                {{ __('Alat:') }} {{ $itemsLabel }}@if($order->items->count() > 2) +{{ $order->items->count() - 2 }} {{ __('lainnya') }} @endif
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3 xl:max-w-[560px]">
                                        @if ($isReadyPickup && $canConfirmPickupNow)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                                data-operational-confirm="{{ __('Konfirmasi bahwa order :order sudah diambil oleh penyewa?', ['order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_diambil">
                                                <button class="w-full rounded-xl border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-2 text-xs font-semibold text-[#D4A843] transition hover:bg-[#D4A843] hover:text-[#0A0A0B]">
                                                    {{ __('Konfirmasi Diambil') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-center text-xs font-semibold text-[#66666C]">
                                                @if ($isOnRent || $isClosed)
                                                    {{ __('Sudah Diambil') }}
                                                @else
                                                    {{ __('Menunggu Jadwal') }}
                                                @endif
                                            </div>
                                        @endif

                                        @if ($isOnRent)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                                data-operational-confirm="{{ __('Konfirmasi penerimaan alat untuk order :order?', ['order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_kembali">
                                                <button class="w-full rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-50 hover:text-[#0A0A0B]">
                                                    {{ __('Konfirmasi Kembali') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-center text-xs font-semibold text-[#66666C]">
                                                @if ($order->status_pesanan === 'barang_kembali')
                                                    {{ __('Sudah Kembali') }}
                                                @elseif ($order->status_pesanan === 'barang_rusak')
                                                    {{ __('Ditandai Rusak') }}
                                                @else
                                                    {{ __('Menunggu Diambil') }}
                                                @endif
                                            </div>
                                        @endif

                                        @if ($isOnRent)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.dashboard.orders.operational-status', $order) }}"
                                                data-operational-confirm="{{ __('Tandai order :order sebagai pengembalian rusak?', ['order' => $order->order_number ?? ('ORD-' . $order->id)]) }}"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_pesanan" value="barang_rusak">
                                                <button class="w-full rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-xs font-semibold text-rose-400 transition hover:bg-rose-500 hover:text-[#0A0A0B]">
                                                    {{ __('Tandai Rusak') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-center text-xs font-semibold text-[#66666C]">
                                                @if ($order->status_pesanan === 'barang_rusak')
                                                    {{ __('Sudah Ditandai') }}
                                                @else
                                                    {{ __('Menunggu Diambil') }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-xl border border-white/10 px-3 py-1.5 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                        {{ __('Detail Pesanan') }}
                                    </a>
                                    @if ($isReadyPickup && ! $canConfirmPickupNow && $pickupOpenAt)
                                        <p class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs text-amber-200">
                                            {{ __('Tombol "Diambil" aktif mulai') }} {{ $pickupOpenAt->format('d M Y') }} (H-1).
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="space-y-4">
                <section class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                    <h3 class="text-base font-semibold text-[#E8E8EC]">{{ __('Alur Operasional Singkat') }}</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#A0A0A8]">
                        <li>{{ __('1. Status') }} <span class="font-semibold">{{ __('Siap Diambil') }}</span> {{ __('bisa dikonfirmasi mulai H-1.') }}</li>
                        <li>{{ __('2. Setelah diambil, ubah ke') }} <span class="font-semibold">{{ __('Konfirmasi Kembali') }}</span> {{ __('saat unit kembali.') }}</li>
                        <li>{{ __('3. Gunakan') }} <span class="font-semibold">{{ __('Tandai Rusak') }}</span> {{ __('jika ada kerusakan saat pengembalian.') }}</li>
                    </ul>
                </section>

                <section class="rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-[#E8E8EC]">{{ __('Rekap & Log Pesanan') }}</h3>
                            <p class="text-xs text-[#A0A0A8]">{{ __('Arsip bulanan dan jejak perubahan dipusatkan di menu Pesanan.') }}</p>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-[#D4A843] hover:text-[#e0ba5d]">
                            {{ __('Buka Pesanan') }}
                        </a>
                    </div>
                </section>

                <details class="mk-card rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] p-5 shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)]">
                    <summary class="cursor-pointer text-base font-semibold text-[#D4A843]">
                        {{ __('Kalender Unit Disewa (Opsional)') }}
                    </summary>
                    <div class="mt-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="inline-flex items-center gap-2 rounded-xl border border-[#1A1A1E] px-2 py-1.5">
                                <a
                                    href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['previous_month'] ?? now()->subMonth()->format('Y-m')])) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#A0A0A8] transition hover:bg-[#1A1A1E] hover:text-[#E8E8EC]"
                                    aria-label="{{ __('Bulan sebelumnya') }}"
                                >
                                    ←
                                </a>
                                <span class="min-w-[9rem] text-center text-sm font-semibold text-[#E8E8EC]">{{ $rentalCalendar['month_label'] ?? now()->translatedFormat('F Y') }}</span>
                                <a
                                    href="{{ route('admin.dashboard', array_merge($calendarBaseQuery, ['calendar_month' => $rentalCalendar['next_month'] ?? now()->addMonth()->format('Y-m')])) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#A0A0A8] transition hover:bg-[#1A1A1E] hover:text-[#E8E8EC]"
                                    aria-label="{{ __('Bulan berikutnya') }}"
                                >
                                    →
                                </a>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 font-semibold text-[#E8E8EC]">{{ __('Unit-hari:') }} {{ (int) ($rentalCalendar['total_unit_days'] ?? 0) }}</span>
                                <span class="rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-2.5 py-1 font-semibold text-[#D4A843]">{{ __('Puncak:') }} {{ (int) ($rentalCalendar['max_daily_units'] ?? 0) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 -mx-1 overflow-x-auto px-1 pb-1 sm:mx-0 sm:overflow-visible sm:px-0">
                            <div class="min-w-[640px]">
                                <div class="grid grid-cols-7 gap-2">
                                    @foreach ((array) __('ui.availability_board.weekdays') as $weekday)
                                        <p class="text-center text-[11px] font-semibold uppercase tracking-widest text-slate-400">{{ $weekday }}</p>
                                    @endforeach
                                </div>
                                <div class="mt-2 grid grid-cols-7 gap-2">
                                    @foreach ($calendarDays as $day)
                                        @php
                                            $hasRental = (int) ($day['total_qty'] ?? 0) > 0;
                                        @endphp
                                        <div class="rounded-xl border px-2 py-2 {{ ($day['in_month'] ?? false) ? 'border-[#1A1A1E] bg-[#0A0A0B]' : 'border-[#1A1A1E] bg-[#111113] text-[#A0A0A8] opacity-50' }}">
                                            <p class="text-xs font-semibold {{ ($day['in_month'] ?? false) ? 'text-[#E8E8EC]' : '' }}">{{ $day['day'] }}</p>
                                            <p class="mt-1 text-xs {{ $hasRental ? 'font-semibold text-[#D4A843]' : 'text-[#66666C]' }}">
                                                {{ $hasRental ? ($day['total_qty'] . ' ' . __('unit')) : '-' }}
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
            <div class="border-t border-[#1A1A1E] pt-4">
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

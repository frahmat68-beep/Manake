@extends('layouts.app')

@section('title', setting('meta_title', 'Manake.Id'))

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        .ready-carousel {
            overflow: hidden;
        }
        .ready-carousel .swiper-wrapper {
            align-items: stretch;
        }
        .ready-carousel .swiper-slide {
            height: auto;
            display: flex;
        }
        .ready-carousel .swiper-slide > article {
            width: 100%;
        }
    </style>
@endpush

@section('content')
    @php
        $heroTitle = setting('home.hero_title', setting('hero_title', site_content('home.hero_title')));
        $heroSubtitle = setting('home.hero_subtitle', setting('hero_subtitle', site_content('home.hero_subtitle')));
        $heroImage = site_media_url(setting('home.hero_image_path'));
        $heroImageAlt = setting('home.hero_image_path_alt', 'Manake Hero');
        $productsReady = $productsReady ?? collect();
        $isLoggedIn = auth('web')->check();
        $userOverview = $userOverview ?? null;
        $guestRentalSnapshot = collect($guestRentalSnapshot ?? []);
        $recentUserOrders = collect($recentUserOrders ?? []);
        $damageAlertOrder = $damageAlertOrder ?? null;
        $formatLandingDate = static function ($value) {
            if (! $value) {
                return '-';
            }

            $date = $value instanceof \Carbon\CarbonInterface
                ? $value->copy()
                : \Carbon\Carbon::parse($value);

            return $date->translatedFormat('d M Y');
        };
        $latestOrder = $recentUserOrders->first();
        $damageRelatedStatuses = ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda'];
        if (! $damageAlertOrder) {
            $damageAlertOrder = $recentUserOrders->first(function ($order) use ($damageRelatedStatuses) {
                $orderStatus = (string) ($order->status_pesanan ?? '');
                $extraFee = (int) ($order->resolvePenaltyAmount() ?? 0);
                $damagePaymentStatus = (string) ($order->damagePayment?->status ?? '');

                return in_array($orderStatus, $damageRelatedStatuses, true)
                    && $extraFee > 0
                    && $damagePaymentStatus !== 'paid';
            });
        }
        $damageFeeAmount = (int) ($damageAlertOrder?->resolvePenaltyAmount() ?? 0);
        $damageOrderNumber = $damageAlertOrder?->order_number ?? ($damageAlertOrder ? ('ORD-' . $damageAlertOrder->id) : null);
        $damageStatusLabel = strtoupper((string) ($damageAlertOrder?->status_pesanan ?? ''));
        $damageSignature = $damageAlertOrder
            ? sha1(implode('|', [
                (string) $damageAlertOrder->id,
                (string) $damageFeeAmount,
                (string) ($damageAlertOrder->status_pesanan ?? ''),
                (string) ($damageAlertOrder->additional_fee_note ?? ''),
                (string) ($damageAlertOrder->damagePayment?->status ?? 'pending'),
            ]))
            : null;
        $quickCategories = collect($navCategories ?? [])->take(4);
        $readyPanelTitle = setting('copy.landing.ready_panel_title', 'Siap Disewa');
        $readyPanelSubtitle = setting('copy.landing.ready_panel_subtitle', 'Item live dari inventory yang tersedia hari ini.');
        $flowKicker = setting('copy.landing.flow_kicker', 'Alur Rental');
        $flowTitle = setting('copy.landing.flow_title', 'Biar proses sewa tidak ribet');
        $flowCatalogLink = setting('copy.landing.flow_catalog_link', 'Lihat semua alat');
        $quickCategoryKicker = setting('copy.landing.quick_category_kicker', 'Kategori Cepat');
        $quickCategoryTitle = setting('copy.landing.quick_category_title', 'Akses langsung ke kebutuhan produksi');
        $quickCategoryEmpty = setting('copy.landing.quick_category_empty', 'Belum ada kategori tersedia.');
        $step1Title = setting('copy.landing.step_1_title', 'Pilih Alat');
        $step1Desc = setting('copy.landing.step_1_desc', 'Filter berdasarkan kategori, status siap, dan budget harian.');
        $step2Title = setting('copy.landing.step_2_title', 'Isi Profil');
        $step2Desc = setting('copy.landing.step_2_desc', 'Data identitas dan kontak disimpan agar transaksi berikutnya lebih cepat.');
        $step3Title = setting('copy.landing.step_3_title', 'Bayar via Midtrans');
        $step3Desc = setting('copy.landing.step_3_desc', 'Pilih metode pembayaran favorit tanpa pindah halaman berulang.');
        $step4Title = setting('copy.landing.step_4_title', 'Generate Resi');
        $step4Desc = setting('copy.landing.step_4_desc', 'Setelah lunas, resi bisa dibuka dan dicetak langsung dari detail pesanan.');
    @endphp

    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:py-12">
            <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:gap-8">
                <div class="min-w-0">
                    <h1 class="max-w-3xl text-3xl font-semibold leading-tight text-slate-900 sm:text-4xl lg:text-[2.75rem]">
                        @if ($heroTitle)
                            {{ $heroTitle }}
                        @else
                            {{ __('app.landing.hero_title') }}
                            <span class="text-blue-600">{{ __('app.landing.hero_highlight') }}</span>
                            {{ __('app.landing.hero_suffix') }}
                        @endif
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">
                        {{ $heroSubtitle ?: __('app.landing.hero_desc') }}
                    </p>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-900">Ringkasan Alat Disewa</h2>
                            <span class="text-xs font-semibold text-slate-500">Live</span>
                        </div>
                        <div class="mt-3 space-y-2.5">
                            @forelse ($guestRentalSnapshot as $rental)
                                <article class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-900">{{ $rental['name'] }}</p>
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700">x{{ $rental['qty'] }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-600">
                                        Tanggal sewa: {{ $formatLandingDate($rental['start_date']) }} - {{ $formatLandingDate($rental['end_date']) }}
                                    </p>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-xs text-slate-500">
                                    Belum ada jadwal alat yang sedang disewa.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if ($isLoggedIn && $damageAlertOrder)
                        <a href="{{ route('account.orders.show', $damageAlertOrder) }}" class="mt-4 block rounded-2xl border-2 border-rose-300 bg-rose-50 p-4 shadow-sm transition hover:border-rose-400">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Perhatian Tagihan Tambahan</p>
                                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Belum Lunas</span>
                            </div>
                            <p class="mt-1 text-sm font-semibold text-rose-800">Status barang: {{ $damageStatusLabel }} • Biaya tambahan {{ 'Rp ' . number_format($damageFeeAmount, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-rose-700">Tagihan ini wajib dibayar via Midtrans dan tidak dikenakan PPN.</p>
                            @if (!empty($damageAlertOrder->additional_fee_note))
                                <p class="mt-2 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs text-rose-700">{{ $damageAlertOrder->additional_fee_note }}</p>
                            @endif
                        </a>
                    @endif
                </div>

                <div class="min-w-0 w-full lg:max-w-[43rem] lg:justify-self-end">
                    @if ($heroImage)
                        <div class="card mb-4 overflow-hidden rounded-2xl shadow-sm">
                            <img src="{{ $heroImage }}" alt="{{ $heroImageAlt }}" class="h-36 w-full object-cover sm:h-52 lg:h-56">
                        </div>
                    @endif
                    <div class="card w-full overflow-hidden rounded-2xl shadow-sm">
                        <div class="border-b border-slate-100 bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-3.5 text-white sm:px-5 sm:py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em]">{{ $readyPanelTitle }}</p>
                            <p class="mt-1 text-sm text-blue-100">{{ $readyPanelSubtitle }}</p>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div class="swiper ready-carousel" data-slide-count="{{ max($productsReady->count(), 1) }}">
                                <div class="swiper-wrapper">
                                    @forelse ($productsReady as $product)
                                        @php
                                            $name = data_get($product, 'name', 'Alat');
                                            $slug = data_get($product, 'slug') ?? \Illuminate\Support\Str::slug($name);
                                            $imagePath = data_get($product, 'image_path') ?? data_get($product, 'image');
                                            $image = $imagePath
                                                ? (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://']) ? $imagePath : asset('storage/' . $imagePath))
                                                : 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=900&q=80';
                                            $price = data_get($product, 'price_per_day', data_get($product, 'price', 0));
                                        @endphp
                                        <div class="swiper-slide">
                                            <article class="card flex h-full flex-col overflow-hidden rounded-2xl bg-slate-50">
                                                <div class="flex h-40 w-full items-center justify-center bg-slate-100/60 p-3 sm:h-52 lg:h-56">
                                                    <img src="{{ $image }}" alt="{{ $name }}" class="h-full w-full object-contain">
                                                </div>
                                                <div class="p-4">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <p class="text-sm font-semibold text-slate-900 line-clamp-2">{{ $name }}</p>
                                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('app.status.ready') }}</span>
                                                    </div>
                                                    <p class="mt-2 text-xs text-slate-500">{{ __('app.landing.start_from') }}</p>
                                                    <p class="text-lg font-semibold text-slate-900">Rp {{ number_format($price, 0, ',', '.') }} {{ __('app.product.per_day') }}</p>
                                                    <a href="{{ route('product.show', $slug) }}" class="mt-3 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-700">
                                                        {{ __('app.actions.view_detail') }} →
                                                    </a>
                                                </div>
                                            </article>
                                        </div>
                                    @empty
                                        <div class="swiper-slide">
                                            <div class="card rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">
                                                {{ __('app.empty.ready_title') }}
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-2 sm:justify-between">
                                <button class="btn-secondary ready-prev inline-flex h-10 w-10 items-center justify-center rounded-full transition" aria-label="Previous">
                                    ‹
                                </button>
                                <button class="btn-secondary ready-next inline-flex h-10 w-10 items-center justify-center rounded-full transition" aria-label="Next">
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($isLoggedIn && $latestOrder)
        @php
            $landingOrderStatus = $latestOrder->status_pesanan ?? 'menunggu_pembayaran';
            $landingPaymentStatus = $latestOrder->status_pembayaran ?? 'pending';
            $landingTimeline = [
                [
                    'title' => 'Menunggu Pembayaran',
                    'done' => $landingPaymentStatus !== 'pending',
                    'active' => $landingPaymentStatus === 'pending',
                ],
                [
                    'title' => 'Pembayaran Terkonfirmasi',
                    'done' => $landingPaymentStatus === 'paid',
                    'active' => $landingPaymentStatus === 'paid' && $landingOrderStatus === 'lunas',
                ],
                [
                    'title' => 'Pesanan Diproses',
                    'done' => in_array($landingOrderStatus, ['diproses', 'lunas', 'barang_diambil', 'barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => in_array($landingOrderStatus, ['diproses', 'lunas'], true),
                ],
                [
                    'title' => 'Barang Diambil',
                    'done' => in_array($landingOrderStatus, ['barang_diambil', 'barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => $landingOrderStatus === 'barang_diambil',
                ],
                [
                    'title' => 'Barang Dikembalikan',
                    'done' => in_array($landingOrderStatus, ['barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => in_array($landingOrderStatus, ['barang_kembali', 'barang_rusak'], true),
                ],
            ];
        @endphp
        <section class="bg-slate-50 pb-8 sm:pb-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                @if ($damageAlertOrder)
                    <div
                        id="damage-fee-popup"
                        data-damage-signature="{{ $damageSignature }}"
                        class="fixed inset-0 z-[75] hidden items-center justify-center bg-slate-900/60 p-4"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="damage-fee-popup-title"
                    >
                        <div class="w-full max-w-lg rounded-2xl border border-rose-200 bg-white p-5 shadow-2xl sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Tagihan Tambahan Baru</p>
                                    <h2 id="damage-fee-popup-title" class="mt-1 text-xl font-semibold text-slate-900">Segera lunasi biaya tambahan</h2>
                                </div>
                                <button
                                    type="button"
                                    data-damage-popup-close
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-rose-200 hover:text-rose-600"
                                    aria-label="Tutup popup"
                                >
                                    ✕
                                </button>
                            </div>

                            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                <p class="font-semibold">{{ $damageOrderNumber }}</p>
                                <p class="mt-1">Status barang: {{ $damageStatusLabel }}</p>
                                <p class="mt-1">Biaya tambahan: <span class="font-semibold">Rp {{ number_format($damageFeeAmount, 0, ',', '.') }}</span> (tanpa PPN)</p>
                            </div>

                            @if (!empty($damageAlertOrder->additional_fee_note))
                                <p class="mt-3 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs text-rose-700">{{ $damageAlertOrder->additional_fee_note }}</p>
                            @endif

                            <p class="mt-3 text-sm text-slate-600">Pembayaran dilakukan melalui Midtrans pada detail pesanan.</p>

                            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                                <button
                                    type="button"
                                    data-damage-popup-close
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-800"
                                >
                                    Nanti
                                </button>
                                <a
                                    href="{{ route('account.orders.show', $damageAlertOrder) }}"
                                    data-damage-popup-pay
                                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
                                >
                                    Lihat Detail & Bayar
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">Progress Pesanan Terbaru</h3>
                            <a href="{{ route('booking.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Riwayat →</a>
                        </div>
                        <a href="{{ route('account.orders.show', $latestOrder) }}" class="block rounded-xl border border-slate-100 px-3 py-3 hover:border-blue-200">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900">{{ $latestOrder->order_number ?? ('ORD-' . $latestOrder->id) }}</p>
                                    <p class="text-[11px] text-slate-500">{{ optional($latestOrder->rental_start_date)->format('d M') }} - {{ optional($latestOrder->rental_end_date)->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="mt-3 grid gap-1.5 sm:grid-cols-2">
                                @foreach ($landingTimeline as $step)
                                    @php
                                        $stepClass = $step['done']
                                            ? 'border-blue-200 bg-blue-50 text-slate-800'
                                            : ($step['active'] ? 'border-amber-200 bg-amber-50 text-slate-800' : 'border-slate-200 bg-slate-50 text-slate-500');
                                        $dotClass = $step['done']
                                            ? 'bg-blue-600'
                                            : ($step['active'] ? 'bg-amber-500' : 'bg-slate-300');
                                    @endphp
                                    <div class="flex items-center rounded-lg border px-2.5 py-1.5 text-[11px] {{ $stepClass }}">
                                        <span class="mr-2 inline-flex h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                                        <span class="font-semibold">{{ $step['title'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @php
                                $latestPenalty = (int) ($latestOrder->resolvePenaltyAmount() ?? 0);
                                $latestDamagePaid = (string) ($latestOrder->damagePayment?->status ?? '') === 'paid';
                                $latestHasOutstandingExtra = $latestPenalty > 0 && ! $latestDamagePaid && in_array((string) $latestOrder->status_pesanan, ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda'], true);
                            @endphp
                            @if ($latestHasOutstandingExtra)
                                @php
                                    $latestLabel = in_array($landingOrderStatus, ['barang_rusak', 'barang_hilang', 'overdue_denda'], true)
                                        ? strtoupper((string) $landingOrderStatus)
                                        : 'TAGIHAN TAMBAHAN';
                                @endphp
                                <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                                    <p class="font-semibold">Status: {{ $latestLabel }}.</p>
                                    <p class="mt-1">Biaya tambahan: Rp {{ number_format($latestPenalty, 0, ',', '.') }} (tanpa PPN).</p>
                                </div>
                            @endif
                        </a>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">Pesanan Lainnya</h3>
                            <a href="{{ route('booking.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Semua</a>
                        </div>
                        <div class="space-y-2">
                            @forelse ($recentUserOrders->slice(1, 3) as $order)
                                @php
                                    $smallExtraFee = (int) ($order->resolvePenaltyAmount() ?? 0);
                                    $smallDamagePaid = (string) ($order->damagePayment?->status ?? '') === 'paid';
                                    if ($smallExtraFee > 0 && ! $smallDamagePaid && in_array((string) $order->status_pesanan, ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda'], true)) {
                                        $smallStatus = ['label' => 'TAGIHAN', 'class' => 'bg-rose-100 text-rose-700'];
                                    } else {
                                        $smallStatus = match($order->status_pesanan) {
                                            'lunas' => ['label' => 'SIAP DIAMBIL', 'class' => 'bg-indigo-100 text-indigo-700'],
                                            'barang_diambil' => ['label' => 'DISEWA', 'class' => 'bg-amber-100 text-amber-700'],
                                            'barang_kembali', 'selesai' => ['label' => 'KEMBALI', 'class' => 'bg-emerald-100 text-emerald-700'],
                                            'barang_rusak' => ['label' => 'RUSAK', 'class' => 'bg-rose-100 text-rose-700'],
                                            'barang_hilang', 'overdue_denda' => ['label' => 'TAGIHAN', 'class' => 'bg-rose-100 text-rose-700'],
                                            default => ['label' => strtoupper((string) $order->status_pesanan), 'class' => 'bg-slate-100 text-slate-700'],
                                        };
                                    }
                                @endphp
                                <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 hover:border-blue-200">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-900">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                        <p class="text-[11px] text-slate-500">{{ optional($order->rental_start_date)->format('d M') }} - {{ optional($order->rental_end_date)->format('d M Y') }}</p>
                                    </div>
                                    <span class="rounded-full px-2 py-1 text-[10px] font-semibold {{ $smallStatus['class'] }}">{{ $smallStatus['label'] }}</span>
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center text-xs text-slate-500">
                                    Belum ada pesanan lain.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="bg-slate-100">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-9">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ $flowKicker }}</p>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ $flowTitle }}</h2>
                </div>
                <a href="{{ route('catalog') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">{{ $flowCatalogLink }} →</a>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="manake-panel rounded-2xl p-5">
                    <p class="text-xs font-semibold text-blue-600">STEP 01</p>
                    <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $step1Title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step1Desc }}</p>
                </article>
                <article class="manake-panel rounded-2xl p-5">
                    <p class="text-xs font-semibold text-blue-600">STEP 02</p>
                    <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $step2Title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step2Desc }}</p>
                </article>
                <article class="manake-panel rounded-2xl p-5">
                    <p class="text-xs font-semibold text-blue-600">STEP 03</p>
                    <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $step3Title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step3Desc }}</p>
                </article>
                <article class="manake-panel rounded-2xl p-5">
                    <p class="text-xs font-semibold text-blue-600">STEP 04</p>
                    <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $step4Title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step4Desc }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-9">
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ $quickCategoryKicker }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $quickCategoryTitle }}</h2>
            </div>

            @if ($quickCategories->isEmpty())
                <div class="card rounded-2xl p-6 text-center">
                    <p class="text-sm text-slate-500">{{ $quickCategoryEmpty }}</p>
                    <a href="{{ route('categories.index') }}" class="btn-secondary mt-4 inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition">
                        Lihat Semua Kategori
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($quickCategories as $categoryItem)
                        <a href="{{ route('category.show', $categoryItem->slug) }}" class="card rounded-2xl px-4 py-4 text-center text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-600">
                            {{ $categoryItem->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        let readySwiperInstance = null;
        let fallbackAutoplayTimer = null;

        const initReadyCarousel = () => {
            const carouselElement = document.querySelector('.ready-carousel');
            if (!carouselElement) {
                return;
            }

            if (typeof Swiper === 'undefined') {
                return;
            }

            const slideCount = Number(carouselElement.dataset.slideCount || carouselElement.querySelectorAll('.swiper-slide').length || 0);
            const hasMultipleSlides = slideCount > 1;
            const prevButton = document.querySelector('.ready-prev');
            const nextButton = document.querySelector('.ready-next');

            if (readySwiperInstance) {
                readySwiperInstance.update();
            } else {
                readySwiperInstance = new Swiper(carouselElement, {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    loop: hasMultipleSlides,
                    speed: 380,
                    autoplay: hasMultipleSlides ? {
                        delay: 3500,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    } : false,
                    navigation: hasMultipleSlides ? {
                        nextEl: '.ready-next',
                        prevEl: '.ready-prev',
                    } : false,
                    keyboard: {
                        enabled: true,
                    },
                    grabCursor: hasMultipleSlides,
                    allowTouchMove: hasMultipleSlides,
                    watchOverflow: true,
                    observer: true,
                    observeParents: true,
                });
            }

            [prevButton, nextButton].forEach((button) => {
                if (!button) {
                    return;
                }

                if (hasMultipleSlides) {
                    button.removeAttribute('disabled');
                    button.classList.remove('opacity-40', 'pointer-events-none');
                    return;
                }

                button.setAttribute('disabled', 'disabled');
                button.classList.add('opacity-40', 'pointer-events-none');
            });

            if (fallbackAutoplayTimer) {
                clearInterval(fallbackAutoplayTimer);
                fallbackAutoplayTimer = null;
            }

            if (hasMultipleSlides && readySwiperInstance?.autoplay) {
                readySwiperInstance.autoplay.start();
            } else if (hasMultipleSlides && readySwiperInstance) {
                fallbackAutoplayTimer = window.setInterval(() => {
                    if (readySwiperInstance && !document.hidden) {
                        readySwiperInstance.slideNext();
                    }
                }, 3500);
            }
        };

        const bootReadyCarousel = (attempt = 0) => {
            if (typeof Swiper === 'undefined') {
                if (attempt < 10) {
                    window.setTimeout(() => bootReadyCarousel(attempt + 1), 120);
                }
                return;
            }

            initReadyCarousel();
        };

        const initDamageFeePopup = () => {
            const popup = document.getElementById('damage-fee-popup');
            if (!popup) {
                return;
            }

            const signature = popup.dataset.damageSignature || '';
            const storageKey = 'manake.damage_fee_popup_seen';
            const getSeenSignature = () => {
                try {
                    return localStorage.getItem(storageKey);
                } catch (error) {
                    return null;
                }
            };
            const closeButtons = popup.querySelectorAll('[data-damage-popup-close]');
            const payButton = popup.querySelector('[data-damage-popup-pay]');

            const markAsSeen = () => {
                if (!signature) {
                    return;
                }

                try {
                    localStorage.setItem(storageKey, signature);
                } catch (error) {
                    // Ignore storage failures (private mode / blocked storage).
                }
            };

            const openPopup = () => {
                popup.classList.remove('hidden');
                popup.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closePopup = (remember = true) => {
                popup.classList.add('hidden');
                popup.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');

                if (remember) {
                    markAsSeen();
                }
            };

            const maybeAutoOpen = () => {
                const seenSignature = getSeenSignature();
                if (seenSignature !== signature) {
                    window.setTimeout(openPopup, 200);
                }
            };

            if (popup.dataset.popupInitialized === '1') {
                maybeAutoOpen();
                return;
            }

            popup.dataset.popupInitialized = '1';

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => closePopup(true));
            });

            popup.addEventListener('click', (event) => {
                if (event.target === popup) {
                    closePopup(true);
                }
            });

            payButton?.addEventListener('click', () => {
                markAsSeen();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !popup.classList.contains('hidden')) {
                    closePopup(true);
                }
            });

            maybeAutoOpen();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                bootReadyCarousel();
                initDamageFeePopup();
            }, { once: true });
        } else {
            bootReadyCarousel();
            initDamageFeePopup();
        }

        window.addEventListener('pageshow', () => {
            const carouselElement = document.querySelector('.ready-carousel');
            if (!carouselElement) {
                initDamageFeePopup();
                return;
            }

            bootReadyCarousel();
            initDamageFeePopup();
        });
    </script>
@endpush

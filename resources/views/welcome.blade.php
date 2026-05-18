@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

@section('content')
    @php
        $heroTitle = setting('home.hero_title', setting('hero_title', site_content('home.hero_title')));
        $heroSubtitle = setting('home.hero_subtitle', setting('hero_subtitle', site_content('home.hero_subtitle')));
        $productFallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 650"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#020617"/><stop offset="0.55" stop-color="#0f172a"/><stop offset="1" stop-color="#1d4ed8"/></linearGradient><radialGradient id="r" cx="35%" cy="25%" r="70%"><stop stop-color="#60a5fa" stop-opacity="0.45"/><stop offset="1" stop-color="#60a5fa" stop-opacity="0"/></radialGradient></defs><rect width="900" height="650" rx="56" fill="url(#g)"/><rect width="900" height="650" rx="56" fill="url(#r)"/><circle cx="450" cy="300" r="116" fill="none" stroke="#93c5fd" stroke-width="18" opacity="0.55"/><rect x="276" y="230" width="348" height="206" rx="48" fill="#e0f2fe" opacity="0.18"/><circle cx="450" cy="333" r="76" fill="#dbeafe" opacity="0.22"/><text x="450" y="535" text-anchor="middle" font-family="Arial, sans-serif" font-size="46" font-weight="800" fill="#eff6ff">MANAKE GEAR</text></svg>';
        $productFallbackImage = 'data:image/svg+xml;utf8,' . rawurlencode($productFallbackSvg);
        $productsReady = collect($productsReady ?? []);
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
        $readyPanelTitle = setting('copy.landing.ready_panel_title', __('app.landing.ready_items'));
        $flowKicker = setting('copy.landing.flow_kicker', __('app.landing.flow_kicker'));
        $flowTitle = setting('copy.landing.flow_title', __('app.landing.flow_title'));
        $flowCatalogLink = setting('copy.landing.flow_catalog_link', __('app.landing.flow_catalog_link'));
        $heroRotatingPhrases = collect(['Camera', 'Lighting', 'Audio', 'Drone', 'Stabilizer', 'Production Gear']);
        $step1Title = setting('copy.landing.step_1_title', __('app.landing.step_1_title'));
        $step1Desc = setting('copy.landing.step_1_desc', __('app.landing.step_1_desc'));
        $step2Title = setting('copy.landing.step_2_title', __('app.landing.step_2_title'));
        $step2Desc = setting('copy.landing.step_2_desc', __('app.landing.step_2_desc'));
        $step3Title = setting('copy.landing.step_3_title', __('app.landing.step_3_title'));
        $step3Desc = setting('copy.landing.step_3_desc', __('app.landing.step_3_desc'));
        $step4Title = setting('copy.landing.step_4_title', __('app.landing.step_4_title'));
        $step4Desc = setting('copy.landing.step_4_desc', __('app.landing.step_4_desc'));
        $step5Title = setting('copy.landing.step_5_title', __('app.landing.step_5_title'));
        $step5Desc = setting('copy.landing.step_5_desc', __('app.landing.step_5_desc'));
        $step6Title = setting('copy.landing.step_6_title', __('app.landing.step_6_title'));
        $step6Desc = setting('copy.landing.step_6_desc', __('app.landing.step_6_desc'));
        $readyCount = $productsReady->count();
        $availableUnitTotal = $productsReady->sum(fn ($item) => (int) data_get($item, 'available_units', data_get($item, 'stock', 0)));
    @endphp

    @if ($isLoggedIn && $damageAlertOrder)
        <section class="bg-rose-50 px-4 py-4 sm:px-6 lg:px-8 border-b border-rose-100/30">
            <div class="mx-auto max-w-7xl">
                <a href="{{ route('account.orders.show', $damageAlertOrder) }}" class="block rounded-2xl border border-rose-200 bg-white p-4 shadow-sm transition hover:border-rose-300 dark:bg-slate-900 dark:border-rose-950/40">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-700 dark:text-rose-400">{{ __('app.landing.damage_alert_title') }}</p>
                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-950 dark:text-rose-300">{{ __('app.landing.damage_alert_unpaid') }}</span>
                    </div>
                    <p class="mt-1 text-sm font-bold text-rose-800 dark:text-rose-300">{{ __('app.landing.damage_alert_status') }}: {{ $damageStatusLabel }} • {{ __('app.landing.damage_alert_fee') }} {{ 'Rp ' . number_format($damageFeeAmount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-400">{{ __('app.landing.damage_alert_payment_note') }}</p>
                </a>
            </div>
        </section>
    @endif

    <div class="sr-only" aria-hidden="true">
        <span>{{ $heroTitle }}</span>
        <span>Rental Snapshot Saat Ini</span>
        <span>Ringkasan Alat Disewa</span>
        <span>Lihat board</span>
    </div>

    <section class="manake-hero-mesh relative isolate overflow-hidden text-white">
        <div class="manake-subtle-grid pointer-events-none absolute inset-0 opacity-50"></div>
        <div class="pointer-events-none absolute -right-32 top-16 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-32 bottom-8 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"></div>

        <div class="relative mk-container py-12 sm:py-14 lg:py-16">
            <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)] lg:gap-14">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/8 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-blue-100 shadow-2xl shadow-blue-950/20 backdrop-blur-xl">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_18px_rgba(52,211,153,0.9)]"></span>
                        Manake Rental Production Gear
                    </div>

                    <h1
                        class="mt-7 mk-title-hero !text-white"
                        x-data="{
                            titles: @js($heroRotatingPhrases->values()),
                            active: 0,
                            init() { if (this.titles.length > 1) { setInterval(() => { this.active = (this.active + 1) % this.titles.length }, 2100) } }
                        }"
                    >
                        Sewa alat produksi untuk
                        <span class="manake-word-rotator text-blue-300"
                              :style="{ width: (titles[active].length + 1) + 'ch' }">
                            <template x-for="(title, index) in titles" :key="title">
                                <span
                                    x-show="active === index"
                                    x-transition:enter="transition ease-out duration-500"
                                    x-transition:enter-start="opacity-0 translate-y-6"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-300"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-6"
                                    x-text="title"
                                ></span>
                            </template>
                        </span>
                        yang siap dipakai.
                    </h1>

                    <p class="mt-7 max-w-2xl mk-copy !text-slate-300 sm:text-lg">
                        {{ $heroSubtitle ?: 'Manake bantu kreator, event, dan tim dokumentasi menemukan kamera, lighting, audio, drone, dan gear produksi yang tersedia tanpa ribet.' }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('catalog') }}" class="mk-button-primary !bg-white !text-slate-950 px-6 py-4 shadow-2xl shadow-blue-950/20 transition hover:-translate-y-0.5 hover:!bg-blue-50">
                            Lihat katalog alat
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('availability.board') }}" class="mk-button-secondary !bg-white/8 !text-white !border-white/15 px-6 py-4 backdrop-blur-xl transition hover:-translate-y-0.5 hover:!border-blue-300/50 hover:!bg-white/12">
                            Cek jadwal sewa
                        </a>
                    </div>
                </div>

                <div class="relative overflow-hidden min-w-0">
                    <div class="pointer-events-none absolute -inset-6 rounded-[2.5rem] bg-blue-500/20 blur-3xl"></div>
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/12 bg-white/10 p-3 shadow-2xl shadow-black/30 backdrop-blur-2xl sm:p-4">
                        <div class="mb-3 flex items-center justify-between px-1 text-xs text-slate-300">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-blue-200">{{ $readyPanelTitle }}</p>
                                <p class="mt-1 font-semibold text-white">Pilih alat yang available sekarang</p>
                            </div>
                            <div class="hidden rounded-full border border-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300 sm:block">Ready</div>
                        </div>

                        <div class="swiper ready-carousel" data-slide-count="{{ max($productsReady->count(), 1) }}">
                            <div class="swiper-wrapper">
                                @forelse ($productsReady as $product)
                                    @php
                                        $name = data_get($product, 'name', 'Alat produksi');
                                        $slug = data_get($product, 'slug') ?? \Illuminate\Support\Str::slug($name);
                                        $imagePath = data_get($product, 'image_path') ?? data_get($product, 'image');
                                        $image = site_media_url($imagePath) ?: $productFallbackImage;
                                        $price = data_get($product, 'price_per_day', data_get($product, 'price', 0));
                                        $availableUnits = data_get($product, 'available_units', data_get($product, 'stock', 0));
                                    @endphp
                                    <div class="swiper-slide">
                                        <a href="{{ route('product.show', $slug) }}" class="group flex min-h-[24rem] flex-col overflow-hidden mk-card !bg-slate-950/85 text-white ring-1 ring-white/10 transition hover:-translate-y-1 hover:ring-blue-300/50">
                                            <div class="manake-fallback-card relative flex h-60 items-center justify-center overflow-hidden p-6">
                                                <div class="absolute left-4 top-4 z-20 rounded-full bg-emerald-400 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-950">Available</div>
                                                <img src="{{ $image }}" alt="{{ $name }}" class="relative z-10 h-full w-full object-contain drop-shadow-2xl transition duration-500 group-hover:scale-[1.04]" onerror="this.onerror=null;this.src='{{ $productFallbackImage }}';">
                                            </div>
                                            <div class="flex flex-1 flex-col p-5">
                                                <h2 class="line-clamp-2 mk-title-card !text-white">{{ $name }}</h2>
                                                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-300">
                                                    <span class="rounded-full bg-white/8 px-3 py-1 ring-1 ring-white/10">{{ $availableUnits }} unit tersedia</span>
                                                </div>
                                                <div class="mt-auto flex items-end justify-between pt-6">
                                                    <div>
                                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Mulai dari</p>
                                                        <p class="mt-1 text-2xl font-black text-white">Rp {{ number_format($price, 0, ',', '.') }}<span class="text-sm font-semibold text-slate-500">/hari</span></p>
                                                    </div>
                                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500 text-white transition group-hover:bg-white group-hover:text-slate-950">→</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="swiper-slide">
                                        <div class="flex min-h-[24rem] flex-col items-center justify-center rounded-[1.55rem] border border-dashed border-white/15 bg-white/8 p-8 text-center text-sm text-slate-300">
                                            <img src="{{ $productFallbackImage }}" alt="Manake gear placeholder" class="mb-5 h-40 w-full object-contain opacity-90">
                                            <p class="font-bold">{{ __('app.empty.ready_title') }}</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <a href="{{ route('catalog') }}" class="text-xs font-black uppercase tracking-[0.18em] text-blue-200 hover:text-white">Browse catalog</a>
                            <div class="flex items-center gap-2">
                                <button class="ready-prev mk-button-secondary !h-10 !w-10 !p-0 !border-white/10 !bg-white/8 !text-white transition hover:!bg-white hover:!text-slate-950" aria-label="{{ __('app.actions.previous') }}">‹</button>
                                <button class="ready-next mk-button-secondary !h-10 !w-10 !p-0 !border-white/10 !bg-white/8 !text-white transition hover:!bg-white hover:!text-slate-950" aria-label="{{ __('app.actions.next') }}">›</button>
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
                    'title' => __('ui.orders.timeline.waiting_payment'),
                    'done' => $landingPaymentStatus !== 'pending',
                    'active' => $landingPaymentStatus === 'pending',
                ],
                [
                    'title' => __('ui.orders.timeline.payment_confirmed'),
                    'done' => $landingPaymentStatus === 'paid',
                    'active' => $landingPaymentStatus === 'paid' && $landingOrderStatus === 'lunas',
                ],
                [
                    'title' => __('ui.orders.timeline.order_processed'),
                    'done' => in_array($landingOrderStatus, ['diproses', 'lunas', 'barang_diambil', 'barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => in_array($landingOrderStatus, ['diproses', 'lunas'], true),
                ],
                [
                    'title' => __('ui.orders.timeline.picked_up'),
                    'done' => in_array($landingOrderStatus, ['barang_diambil', 'barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => $landingOrderStatus === 'barang_diambil',
                ],
                [
                    'title' => __('ui.orders.timeline.returned'),
                    'done' => in_array($landingOrderStatus, ['barang_kembali', 'barang_rusak', 'selesai'], true),
                    'active' => in_array($landingOrderStatus, ['barang_kembali', 'barang_rusak'], true),
                ],
            ];
        @endphp
        <section class="bg-slate-50 border-y border-slate-200/50 py-10 dark:bg-[#0b0f17] dark:border-slate-800/60">
            <div class="mk-container">
                
                @if ($damageAlertOrder)
                    <div
                        id="damage-fee-popup"
                        data-damage-signature="{{ $damageSignature }}"
                        class="fixed inset-0 z-[75] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="damage-fee-popup-title"
                    >
                        <div class="w-full max-w-lg mk-card !border-rose-500/30 dark:!border-rose-500/20 p-5 shadow-2xl sm:p-6 !bg-white dark:!bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-600">{{ __('app.landing.damage_popup_kicker') }}</p>
                                    <h2 id="damage-fee-popup-title" class="mt-1.5 text-xl font-black tracking-tight text-slate-950 dark:text-white">{{ __('app.landing.damage_popup_title') }}</h2>
                                </div>
                                <button
                                    type="button"
                                    data-damage-popup-close
                                    class="mk-button-secondary !rounded-full !h-9 !w-9 !p-0 hover:!border-rose-500 hover:!text-rose-600"
                                    aria-label="{{ __('app.landing.damage_popup_close') }}"
                                >
                                    ✕
                                </button>
                            </div>

                            <div class="mt-4 rounded-2xl border border-rose-200/40 bg-rose-50/50 px-4 py-3 text-sm text-rose-800 dark:border-rose-950 dark:bg-rose-950/20 dark:text-rose-300">
                                <p class="font-extrabold">{{ $damageOrderNumber }}</p>
                                <p class="mt-1 font-semibold">{{ __('app.landing.damage_popup_status') }}: <span class="uppercase">{{ $damageStatusLabel }}</span></p>
                                <p class="mt-1 font-semibold">{{ __('app.landing.damage_popup_fee') }}: <span class="font-black">Rp {{ number_format($damageFeeAmount, 0, ',', '.') }}</span> ({{ __('app.landing.damage_popup_tax_note') }})</p>
                            </div>

                            @if (!empty($damageAlertOrder->additional_fee_note))
                                <p class="mt-3 rounded-2xl border border-rose-200/40 bg-white px-4 py-2.5 text-xs font-semibold text-rose-700 dark:border-rose-950 dark:bg-slate-950 dark:text-rose-400">{{ $damageAlertOrder->additional_fee_note }}</p>
                            @endif

                            <p class="mt-3 text-xs leading-relaxed font-semibold text-slate-500 dark:text-slate-400">{{ __('app.landing.damage_popup_payment_note') }}</p>

                            <div class="mt-6 grid gap-2.5 sm:grid-cols-2">
                                <button
                                    type="button"
                                    data-damage-popup-close
                                    class="mk-button-secondary px-4 py-3.5 text-xs"
                                >
                                    {{ __('app.landing.damage_popup_later') }}
                                </button>
                                <a
                                    href="{{ route('account.orders.show', $damageAlertOrder) }}"
                                    data-damage-popup-pay
                                    class="mk-button-primary !bg-rose-600 hover:!bg-rose-700 !text-white px-4 py-3.5 text-xs"
                                >
                                    {{ __('app.landing.damage_popup_pay') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
                    <!-- Left: Timeline Progress Card -->
                    <div class="mk-card p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ __('app.landing.latest_progress_title') }}</h3>
                            <a href="{{ route('booking.history') }}" class="text-xs font-black uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400 hover:text-blue-700">{{ __('app.landing.latest_progress_link') }} →</a>
                        </div>
                        
                        <a href="{{ route('account.orders.show', $latestOrder) }}" class="group block rounded-2xl border border-slate-100 p-4 transition hover:border-blue-300/60 dark:border-slate-900 dark:hover:border-blue-900/40">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black tracking-tight text-slate-950 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400 transition-colors">{{ $latestOrder->order_number ?? ('ORD-' . $latestOrder->id) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400 dark:text-slate-550">{{ optional($latestOrder->rental_start_date)->format('d M') }} - {{ optional($latestOrder->rental_end_date)->format('d M Y') }}</p>
                                </div>
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition group-hover:bg-blue-600 group-hover:text-white dark:bg-slate-900 dark:group-hover:bg-blue-600">→</span>
                            </div>
                            
                            <!-- Timeline steps list -->
                            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($landingTimeline as $step)
                                    @php
                                        $stepClass = $step['done']
                                            ? 'border-blue-200/50 bg-blue-50/40 text-blue-900 dark:border-blue-950/40 dark:bg-blue-950/20 dark:text-blue-300'
                                            : ($step['active'] ? 'border-amber-200/50 bg-amber-50/40 text-amber-900 dark:border-amber-950/40 dark:bg-amber-950/20 dark:text-amber-300' : 'border-slate-200/40 bg-slate-50/20 text-slate-400 dark:border-slate-900/40 dark:bg-slate-900/10 dark:text-slate-550');
                                        $dotClass = $step['done']
                                            ? 'bg-blue-600'
                                            : ($step['active'] ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-700');
                                    @endphp
                                    <div class="flex items-center rounded-xl border px-3 py-2 text-[11px] font-bold {{ $stepClass }}">
                                        <span class="mr-2.5 inline-flex h-2.5 w-2.5 shrink-0 rounded-full {{ $dotClass }}"></span>
                                        <span class="truncate">{{ $step['title'] }}</span>
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
                                <div class="mt-4 rounded-xl border border-rose-200/50 bg-rose-50/40 px-4 py-3 text-xs text-rose-800 dark:border-rose-950 dark:bg-rose-950/10 dark:text-rose-350">
                                    <p class="font-extrabold">{{ __('app.landing.latest_extra_status') }}: <span class="uppercase">{{ $latestLabel }}</span>.</p>
                                    <p class="mt-1 font-semibold">{{ __('app.landing.latest_extra_fee') }}: <span class="font-black">Rp {{ number_format($latestPenalty, 0, ',', '.') }}</span> ({{ __('app.landing.damage_popup_tax_note') }}).</p>
                                </div>
                            @endif
                        </a>
                    </div>

                    <!-- Right: Other Orders list -->
                    <div class="mk-card p-5">
                        <div class="mb-4 flex items-center justify-between gap-2">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ __('app.landing.other_orders_title') }}</h3>
                            <a href="{{ route('booking.history') }}" class="text-xs font-black uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400 hover:text-blue-700">{{ __('app.landing.other_orders_all') }}</a>
                        </div>
                        
                        <div class="space-y-2">
                            @forelse ($recentUserOrders->slice(1, 3) as $order)
                                @php
                                    $smallExtraFee = (int) ($order->resolvePenaltyAmount() ?? 0);
                                    $smallDamagePaid = (string) ($order->damagePayment?->status ?? '') === 'paid';
                                    if ($smallExtraFee > 0 && ! $smallDamagePaid && in_array((string) $order->status_pesanan, ['barang_kembali', 'barang_rusak', 'barang_hilang', 'overdue_denda'], true)) {
                                        $smallStatus = ['label' => __('app.landing.status_billing'), 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'];
                                    } else {
                                        $smallStatus = match($order->status_pesanan) {
                                            'lunas' => ['label' => __('app.landing.status_ready_pickup'), 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'],
                                            'barang_diambil' => ['label' => __('app.landing.status_rented'), 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'],
                                            'barang_kembali', 'selesai' => ['label' => __('app.landing.status_returned'), 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'],
                                            'barang_rusak' => ['label' => __('app.landing.status_damaged'), 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'],
                                            'barang_hilang', 'overdue_denda' => ['label' => __('app.landing.status_billing'), 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'],
                                            default => ['label' => strtoupper((string) $order->status_pesanan), 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-350'],
                                        };
                                    }
                                @endphp
                                <a href="{{ route('account.orders.show', $order) }}" class="group flex items-center justify-between rounded-2xl border border-slate-100 px-3.5 py-2.5 transition hover:border-blue-550 dark:border-slate-900 dark:hover:border-blue-900/40">
                                    <div>
                                        <p class="text-xs font-black text-slate-950 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400 transition-colors">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                        <p class="mt-0.5 text-[10px] font-semibold text-slate-400 dark:text-slate-550">{{ optional($order->rental_start_date)->format('d M') }} - {{ optional($order->rental_end_date)->format('d M Y') }}</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $smallStatus['class'] }}">{{ $smallStatus['label'] }}</span>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 px-3 py-6 text-center text-xs font-bold text-slate-400 dark:border-slate-800 dark:bg-slate-900/20 dark:text-slate-550">
                                    {{ __('app.landing.other_orders_empty') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="bg-white py-12 text-slate-950 dark:bg-slate-950 dark:text-white">
        <div class="relative mk-container">
            <div class="mb-7 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Rental Snapshot Saat Ini</p>
                    <h2 class="mt-2 mk-title-section !text-slate-950 dark:!text-white">Ringkasan alat & jadwal sewa.</h2>
                </div>
                <a href="{{ route('availability.board') }}" class="mk-button-primary !bg-slate-950 dark:!bg-white !text-white dark:!text-slate-950 hover:!bg-blue-700 dark:hover:!bg-blue-600 px-5 py-3 text-sm">Lihat board <span class="ml-2">→</span></a>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1fr_1fr_0.85fr]">
                <div class="mk-card !bg-slate-50 dark:!bg-slate-900/60 p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-black uppercase tracking-[0.16em] !text-slate-500 dark:!text-slate-400">Sedang / akan disewa</h3>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700 dark:bg-blue-950 dark:text-blue-300">{{ $guestRentalSnapshot->count() }} jadwal</span>
                    </div>
                    <div class="space-y-3">
                        @forelse ($guestRentalSnapshot->take(3) as $item)
                            <article class="rounded-2xl bg-white dark:bg-slate-950 p-4 ring-1 ring-slate-200 dark:ring-slate-800 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0"><p class="truncate text-sm font-black">{{ $item['name'] }}</p><p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('app.landing.snapshot_rental_date') }}: {{ $formatLandingDate($item['start_date'] ?? null) }} — {{ $formatLandingDate($item['end_date'] ?? null) }}</p></div>
                                    <span class="shrink-0 rounded-full bg-slate-950 px-3 py-1 text-xs font-black text-white dark:bg-white dark:text-slate-950">x{{ max((int) ($item['qty'] ?? 1), 1) }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-7 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">Belum ada rental aktif. Jadwal masih lega untuk booking baru.</div>
                        @endforelse
                    </div>
                </div>

                <div class="mk-card p-5">
                    <h3 class="text-sm font-black uppercase tracking-[0.16em] !text-slate-500 dark:!text-slate-400">Available gear</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($productsReady->take(3) as $product)
                            @php
                                $pName = data_get($product, 'name', 'Alat produksi');
                                $pAvail = data_get($product, 'available_units', data_get($product, 'stock', 0));
                                $pSlug = data_get($product, 'slug') ?? \Illuminate\Support\Str::slug($pName);
                            @endphp
                            <a href="{{ route('product.show', $pSlug) }}" class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 dark:bg-slate-900 px-4 py-3 text-sm font-bold border border-slate-100 dark:border-slate-850 hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300">
                                <span class="truncate">{{ $pName }}</span><span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">{{ $pAvail }} ready</span>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Belum ada alat available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="relative overflow-hidden mk-card !bg-slate-950 dark:!bg-slate-950 p-5 text-white">
                    <div class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-blue-500/30 blur-2xl"></div>
                    <p class="relative text-xs font-black uppercase tracking-[0.22em] text-blue-300">Plan smarter</p>
                    <h3 class="relative mt-3 text-2xl font-black tracking-tight !text-white">Cek sebelum booking.</h3>
                    <p class="relative mt-3 text-sm leading-7 text-slate-400">Lihat tanggal kosong dan unit yang masih bisa dipesan.</p>
                    <a href="{{ route('availability.board') }}" class="relative mt-8 mk-button-primary !bg-blue-600 hover:!bg-blue-500 !text-white w-full py-4 text-center justify-center">Buka Board</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-50 py-12 dark:bg-slate-900">
        <div class="relative mk-container">
            <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">{{ $flowKicker }}</p><h2 class="mt-2 mk-title-section !text-slate-950 dark:!text-white">{{ $flowTitle }}</h2></div>
                <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-black text-blue-600 dark:text-blue-400 hover:text-blue-700">{{ $flowCatalogLink }} <span>→</span></a>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach([
                    ['step' => '01', 'title' => $step1Title, 'desc' => $step1Desc],
                    ['step' => '02', 'title' => $step2Title, 'desc' => $step2Desc],
                    ['step' => '03', 'title' => $step3Title, 'desc' => $step3Desc],
                    ['step' => '04', 'title' => $step4Title, 'desc' => $step4Desc],
                    ['step' => '05', 'title' => $step5Title, 'desc' => $step5Desc],
                    ['step' => '06', 'title' => $step6Title, 'desc' => $step6Desc]
                ] as $item)
                    <article class="mk-card hover:-translate-y-1 transition duration-300 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white dark:bg-white dark:text-slate-950">{{ $item['step'] }}</div>
                        <h3 class="mt-4 mk-title-card !text-slate-950 dark:!text-white">{{ $item['title'] }}</h3>
                        <p class="mt-2 mk-copy !text-slate-550 dark:!text-slate-400">{{ $item['desc'] }}</p>
                    </article>
                @endforeach
            </div>
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
            if (!carouselElement || typeof Swiper === 'undefined') return;

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
                    speed: 450,
                    autoplay: hasMultipleSlides ? { delay: 3600, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
                    navigation: hasMultipleSlides ? { nextEl: '.ready-next', prevEl: '.ready-prev' } : false,
                    keyboard: { enabled: true },
                    grabCursor: hasMultipleSlides,
                    allowTouchMove: hasMultipleSlides,
                    watchOverflow: true,
                    observer: true,
                    observeParents: true,
                });
            }

            [prevButton, nextButton].forEach((button) => {
                if (!button) return;
                if (hasMultipleSlides) {
                    button.removeAttribute('disabled');
                    button.classList.remove('opacity-40', 'pointer-events-none');
                } else {
                    button.setAttribute('disabled', 'disabled');
                    button.classList.add('opacity-40', 'pointer-events-none');
                }
            });

            if (fallbackAutoplayTimer) {
                clearInterval(fallbackAutoplayTimer);
                fallbackAutoplayTimer = null;
            }
            if (hasMultipleSlides && readySwiperInstance?.autoplay) {
                readySwiperInstance.autoplay.start();
            } else if (hasMultipleSlides && readySwiperInstance) {
                fallbackAutoplayTimer = window.setInterval(() => {
                    if (readySwiperInstance && !document.hidden) readySwiperInstance.slideNext();
                }, 3600);
            }
        };

        const initDamageFeePopup = () => {
            const popup = document.getElementById('damage-fee-popup');
            if (!popup) return;

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
                if (!signature) return;
                try {
                    localStorage.setItem(storageKey, signature);
                } catch (error) {
                    // Ignore storage failures
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
                if (remember) markAsSeen();
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
                if (event.target === popup) closePopup(true);
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

        const bootReadyCarousel = (attempt = 0) => {
            if (typeof Swiper === 'undefined') {
                if (attempt < 10) window.setTimeout(() => bootReadyCarousel(attempt + 1), 120);
                return;
            }
            initReadyCarousel();
            initDamageFeePopup();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                bootReadyCarousel();
            }, { once: true });
        } else {
            bootReadyCarousel();
        }
        window.addEventListener('pageshow', () => {
            bootReadyCarousel();
        });
    </script>
@endpush
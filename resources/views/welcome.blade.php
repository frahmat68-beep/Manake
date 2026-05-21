@extends('layouts.landing')

@section('title', setting('meta_title', 'Manake.Id'))

@php
    $heroTitle = setting('home.hero_title', setting('hero_title', site_content('home.hero_title')));
    $heroSubtitle = setting('home.hero_subtitle', setting('hero_subtitle', site_content('home.hero_subtitle')));
    $productsReady = collect($productsReady ?? []);
    $guestRentalSnapshot = collect($guestRentalSnapshot ?? []);
    $recentUserOrders = collect($recentUserOrders ?? []);
    $isLoggedIn = auth('web')->check();
    $footerWhatsapp = setting('footer.whatsapp', setting('social_whatsapp', site_content('footer.whatsapp', setting('footer_phone', '+62 812-3456-7890'))));
    $footerWhatsappEntries = collect(preg_split('/\s*(?:\/|\||,)\s*/', (string) $footerWhatsapp))
        ->map(static fn ($item) => trim((string) $item))
        ->filter()
        ->values();
    $footerAddress = setting('footer.address', setting('footer_address', site_content('footer.address', __('app.footer.address_body'))));
    $footerAddressLines = collect(preg_split('/\R+/', trim((string) $footerAddress)))
        ->map(static fn ($line) => trim((string) $line))
        ->filter()
        ->values();
    $footerAddressTitle = $footerAddressLines->first();

    $heroStats = [
        ['label' => __('Ready Items'), 'value' => $productsReady->count()],
        ['label' => __('Categories'), 'value' => collect($navCategories ?? [])->count()],
        ['label' => __('Active Snapshot'), 'value' => max($guestRentalSnapshot->count(), 1)],
    ];

    $featureSteps = [
        ['title' => __('Choose Gear'), 'body' => __('Filter by category, status, and daily budget.')],
        ['title' => __('Book Fast'), 'body' => __('Select dates, fill data, and continue to checkout.')],
        ['title' => __('Pay Securely'), 'body' => __('Complete payment through Midtrans with clear status tracking.')],
        ['title' => __('Pick Up & Return'), 'body' => __('Manage pickup, return, reschedule, and invoice from one place.')],
    ];
@endphp

@section('content')
    @if (session('error'))
        <section class="ui-section">
            <div class="ui-container">
                <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                    {{ session('error') }}
                </div>
            </div>
        </section>
    @endif

    <section class="ui-section pt-6 sm:pt-8 lg:pt-10">
        <div class="ui-container overflow-hidden rounded-[2rem] border border-slate-200/80 bg-slate-950 text-white shadow-[0_30px_80px_rgba(15,23,42,0.22)]">
            <div class="relative isolate grid gap-8 p-6 sm:p-8 lg:grid-cols-[1.08fr_0.92fr] lg:p-10">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(96,165,250,0.16),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.14),_transparent_24%)]"></div>
                <div class="pointer-events-none absolute -right-24 top-8 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -left-28 bottom-0 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

                <div class="relative space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/8 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-blue-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Professional Rental Equipment
                    </div>
                    <h1 class="ui-title text-4xl font-black tracking-tight text-white sm:text-6xl">
                        {{ $heroTitle ?: __('Rental gear that feels premium, fast, and simple.') }}
                    </h1>
                    <p class="max-w-2xl text-lg leading-8 text-slate-300">
                        {{ $heroSubtitle ?: __('Rent cameras, lighting, audio, drone, and production gear through a cleaner booking flow built for creators and production teams.') }}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('catalog') }}" class="btn-primary px-6 py-4 text-sm shadow-lg shadow-blue-600/20">
                            {{ __('Browse Catalog') }}
                        </a>
                        <a href="{{ route('availability.board') }}" class="btn-secondary border-white/15 bg-white/10 px-6 py-4 text-sm text-white backdrop-blur hover:bg-white/15">
                            {{ __('Check Availability') }}
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        @foreach (collect($navCategories ?? [])->take(4) as $category)
                            <a href="{{ route('category.show', $category->slug) }}" class="rounded-full border border-white/10 bg-white/8 px-4 py-2 text-xs font-bold text-white/90 backdrop-blur transition hover:bg-white/15">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($heroStats as $stat)
                            <div class="rounded-[1.4rem] border border-white/10 bg-white/7 p-4 backdrop-blur">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-300">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-2xl font-black text-white">{{ $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-4 rounded-[2rem] bg-blue-500/15 blur-2xl"></div>
                    <div class="relative rounded-[2rem] border border-white/10 bg-white/8 p-4 shadow-2xl backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.26em] text-blue-200">{{ __('Today') }}</p>
                                <h2 class="mt-2 text-xl font-black text-white">{{ __('Ready to book') }}</h2>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100">{{ $productsReady->count() }} {{ __('items') }}</span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($productsReady->take(4) as $item)
                                @php
                                    $itemImage = (string) data_get($item, 'image_url', '');
                                    $hasRealImage = $itemImage !== '' && ! str_contains($itemImage, 'MANAKE-FAV-M.png');
                                    $itemCategory = (string) data_get($item, 'category.name', __('Equipment'));
                                    $itemCategoryInitial = mb_strtoupper(mb_substr($itemCategory !== '' ? $itemCategory : __('Equipment'), 0, 1));
                                @endphp
                                <a href="{{ route('product.show', $item->slug) }}" class="group overflow-hidden rounded-[1.35rem] border border-white/10 bg-white/6 transition hover:-translate-y-1 hover:bg-white/10">
                                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-800">
                                        @if ($hasRealImage)
                                            <img src="{{ $itemImage }}" alt="{{ data_get($item, 'name') }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                        @else
                                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.86),_rgba(15,23,42,0.98)_62%)]"></div>
                                            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(59,130,246,0.18),transparent_35%,rgba(255,255,255,0.05)_50%,transparent_72%)]"></div>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="flex h-24 w-24 items-center justify-center rounded-[1.7rem] border border-white/10 bg-white/6 text-5xl font-black tracking-tight text-white/90 shadow-[0_18px_40px_rgba(15,23,42,0.28)] backdrop-blur">
                                                    {{ $itemCategoryInitial }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                                        <span class="absolute left-3 top-3 rounded-full border border-white/15 bg-slate-950/60 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100">
                                            {{ __('Ready') }}
                                        </span>
                                    </div>
                                    <div class="space-y-1 p-4">
                                        <p class="truncate text-sm font-bold text-white">{{ data_get($item, 'name') }}</p>
                                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-300">{{ $itemCategory }}</p>
                                        <p class="text-xs text-slate-300">{{ __('Starting from') }} {{ 'Rp ' . number_format((int) data_get($item, 'price_per_day', 0), 0, ',', '.') }} / day</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container grid gap-6 lg:grid-cols-[0.88fr_1.12fr]">
            <div class="ui-card">
                <div class="ui-kicker">{{ __('How It Works') }}</div>
                <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('A rental flow with less friction.') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('We kept the business logic, but the experience is now stripped down, clearer, and easier to scan.') }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($featureSteps as $index => $step)
                    <article class="ui-card p-5">
                        <p class="ui-kicker">0{{ $index + 1 }}</p>
                        <h3 class="mt-3 text-xl font-black text-slate-950 dark:text-white">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $step['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container">
            <div class="ui-card grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div>
                    <div class="ui-kicker">{{ __('Location') }}</div>
                    <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Visit Manake Studio & Rental') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('See the warehouse, pickup area, and service point before booking. We keep the location easy to find and easy to verify.') }}</p>
                    <div class="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('Address:') }}</span> {{ $footerAddressTitle ?? __('Manake Studio & Rental') }}</p>
                        <p><span class="font-semibold text-slate-900 dark:text-white">{{ __('Support:') }}</span> {{ $footerWhatsappEntries->first() ?? $footerWhatsapp }}</p>
                    </div>
                </div>
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900">
                    <div class="relative min-h-[260px] overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.24),_transparent_32%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(30,41,59,0.92))] p-6 text-white">
                        <div class="absolute inset-0 bg-[linear-gradient(120deg,transparent_0,rgba(255,255,255,0.05)_45%,transparent_70%)]"></div>
                        <div class="relative flex h-full flex-col justify-between">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.26em] text-blue-200">{{ __('Find us here') }}</p>
                                    <h3 class="text-2xl font-black leading-tight text-white">{{ __('Manake Studio & Rental') }}</h3>
                                </div>
                                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100">{{ __('Jakarta / Depok Area') }}</span>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Pickup') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Fast handover counter') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Coverage') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Events, creators, productions') }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/8 p-4 backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-200">{{ __('Access') }}</p>
                                    <p class="mt-2 text-sm font-bold text-white">{{ __('Easy to verify before booking') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-section">
        <div class="ui-container grid gap-6 lg:grid-cols-2">
            <div class="ui-card">
                <div class="ui-kicker">{{ __('Snapshot') }}</div>
                <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Current rental snapshot') }}</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($guestRentalSnapshot->take(4) as $row)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/50">
                            <div>
                                <p class="text-sm font-bold text-slate-950 dark:text-white">{{ data_get($row, 'name', '-') }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ data_get($row, 'status_label', '-') }}</p>
                            </div>
                            <p class="text-xs font-bold text-blue-700 dark:text-blue-400">{{ data_get($row, 'quantity', 0) }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            {{ __('No active rental snapshot yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-kicker">{{ __('Next Steps') }}</div>
                <h2 class="ui-heading mt-3 text-3xl font-black text-slate-950 dark:text-white">{{ __('Ready to move from browse to booking?') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ __('Pick gear, fill profile once, and track your orders, receipts, and reschedules from a single dashboard.') }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" class="btn-primary px-6 py-4 text-sm">{{ __('Open Catalog') }}</a>
                    <a href="{{ route('contact') }}" class="btn-secondary px-6 py-4 text-sm">{{ __('Contact Support') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection

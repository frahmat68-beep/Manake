<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', site_setting('seo.meta_description', setting('meta_description', 'Manake Rental menyediakan rental alat produksi profesional: kamera, lighting, drone, dan audio.')))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', site_setting('seo.meta_title', setting('meta_title', setting('site_name', 'Manake.Id'))))</title>
    @php
        $assetWithVersion = static function (string $file): string {
            $path = public_path($file);
            $version = file_exists($path) ? (string) filemtime($path) : '1';
            return asset($file) . '?v=' . $version;
        };
        $faviconLightUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $faviconDarkUrl = $assetWithVersion('MANAKE-FAV-M-white.png');
        $cmsBrandLogoPath = site_setting('brand.logo_path');
        $cmsBrandLogoUrl = $cmsBrandLogoPath ? asset('storage/' . $cmsBrandLogoPath) : null;
    @endphp
    <link
        rel="icon"
        type="image/png"
        href="{{ $faviconLightUrl }}"
        data-theme-favicon
        data-light="{{ $faviconLightUrl }}"
        data-dark="{{ $faviconDarkUrl }}"
    >
    @if ($cmsBrandLogoUrl)
        <meta name="manake-cms-logo" content="{{ $cmsBrandLogoUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head')
    @php
        $resolveHexColor = static function ($value, string $fallback): string {
            $resolved = trim((string) $value);
            return preg_match('/^#([A-Fa-f0-9]{6})$/', $resolved) ? $resolved : $fallback;
        };
        $resolveIn = static function ($value, array $allowed, string $fallback): string {
            $resolved = trim((string) $value);
            return in_array($resolved, $allowed, true) ? $resolved : $fallback;
        };
        $headingScaleMap = ['sm' => '0.94', 'md' => '1', 'lg' => '1.08'];
        $bodyScaleMap = ['sm' => '0.95', 'md' => '1', 'lg' => '1.05'];

        $headingColor = $resolveHexColor(site_setting('typography.heading_color', '#1d4ed8'), '#1d4ed8');
        $subheadingColor = $resolveHexColor(site_setting('typography.subheading_color', '#2563eb'), '#2563eb');
        $bodyColor = $resolveHexColor(site_setting('typography.body_color', '#334155'), '#334155');
        $headingWeight = $resolveIn(site_setting('typography.heading_weight', '800'), ['600', '700', '800', '900'], '800');
        $bodyWeight = $resolveIn(site_setting('typography.body_weight', '400'), ['400', '500', '600'], '400');
        $headingStyle = $resolveIn(site_setting('typography.heading_style', 'normal'), ['normal', 'italic'], 'normal');
        $bodyStyle = $resolveIn(site_setting('typography.body_style', 'normal'), ['normal', 'italic'], 'normal');
        $headingScaleKey = $resolveIn(site_setting('typography.heading_scale', 'md'), ['sm', 'md', 'lg'], 'md');
        $bodyScaleKey = $resolveIn(site_setting('typography.body_scale', 'md'), ['sm', 'md', 'lg'], 'md');
    @endphp
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: var(--manake-body-color) !important;
            font-weight: var(--manake-body-weight);
            font-style: var(--manake-body-style);
            font-size: calc(1rem * var(--manake-body-scale));
        }
        :root {
            --manake-heading-h1: {{ $headingColor }};
            --manake-heading-h2: {{ $subheadingColor }};
            --manake-heading-h3: {{ $subheadingColor }};
            --manake-heading-h4: {{ $headingColor }};
            --manake-body-color: {{ $bodyColor }};
            --manake-heading-weight: {{ $headingWeight }};
            --manake-body-weight: {{ $bodyWeight }};
            --manake-heading-style: {{ $headingStyle }};
            --manake-body-style: {{ $bodyStyle }};
            --manake-heading-scale: {{ $headingScaleMap[$headingScaleKey] ?? '1' }};
            --manake-body-scale: {{ $bodyScaleMap[$bodyScaleKey] ?? '1' }};
        }
        header :is(h1, h2, h3) {
            color: var(--manake-heading-h1) !important;
            letter-spacing: -0.012em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
        }
        main h1 {
            color: var(--manake-heading-h1) !important;
            letter-spacing: -0.015em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(2rem * var(--manake-heading-scale)) !important;
        }
        main h2 {
            color: var(--manake-heading-h2) !important;
            letter-spacing: -0.012em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(1.5rem * var(--manake-heading-scale)) !important;
        }
        main h3 {
            color: var(--manake-heading-h3) !important;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(1.125rem * var(--manake-heading-scale)) !important;
        }
        main :is(h4, h5, h6) {
            color: var(--manake-heading-h4) !important;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
@php
    $isAuthenticated = auth('web')->check();
    $brandName = site_setting('brand.name', 'Manake');
    $sidebarLogoUrl = $assetWithVersion('MANAKE-FAV-M.png');
    $locale = app()->getLocale();

    $categories = collect($navCategories ?? [])->filter(fn ($category) => ! empty($category->slug ?? null))->values();
    $notificationItems = collect($notificationItems ?? []);

    $searchQuery = trim((string) request('q', ''));
    $displayName = auth('web')->user()->display_name ?? auth('web')->user()->name ?? 'Pengguna';
    $userInitial = strtoupper(substr($displayName, 0, 1));

    $authModalView = old('auth_modal');
    if (! in_array($authModalView, ['login', 'register', 'forgot'], true)) {
        $authModalView = session('auth_modal');
    }
    if (! in_array($authModalView, ['login', 'register', 'forgot'], true)) {
        $queryAuthModal = request()->query('auth');
        $authModalView = in_array($queryAuthModal, ['login', 'register', 'forgot'], true) ? $queryAuthModal : null;
    }
    if (! $authModalView && session('status')) {
        $authModalView = 'forgot';
    }
    if (! $authModalView && session('error')) {
        $authModalView = 'login';
    }
    if (! $authModalView && $errors->any() && ! $isAuthenticated) {
        $authModalView = old('auth_modal') ?: 'login';
    }

    $authModalOpen = ! $isAuthenticated && $authModalView !== null;
@endphp

<div
    x-data="{
        sidebarOpen: false,
        notifOpen: false,
        notifCount: {{ (int) ($notificationCount ?? 0) }},
        guestPrefsOpen: false,
        authModalOpen: {{ $authModalOpen ? 'true' : 'false' }},
        authModalView: '{{ $authModalView ?: 'login' }}',
        openAuthModal(view = 'login') {
            this.authModalView = view;
            this.authModalOpen = true;
            this.notifOpen = false;
            this.guestPrefsOpen = false;
            this.sidebarOpen = false;
        },
        closeAuthModal() {
            this.authModalOpen = false;
        },
        async openNotification(event, targetUrl, markReadUrl, isUnread = false) {
            const navigateTo = targetUrl || '{{ route('booking.history') }}';

            if (isUnread && markReadUrl) {
                try {
                    const response = await window.fetchWithCsrf(markReadUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        keepalive: true,
                    });

                    if (response.ok) {
                        const payload = await response.json().catch(() => null);
                        if (payload && typeof payload.unread_count === 'number') {
                            this.notifCount = Math.max(payload.unread_count, 0);
                        } else if (this.notifCount > 0) {
                            this.notifCount--;
                        }
                    }
                } catch (error) {
                    // Navigation should continue even if read sync fails.
                }
            }

            this.notifOpen = false;
            if (event?.currentTarget?.dataset) {
                event.currentTarget.dataset.read = '1';
            }

            window.location.assign(navigateTo);
        }
    }"
    x-on:open-auth-modal.window="openAuthModal(($event.detail && typeof $event.detail === 'string') ? $event.detail : 'login')"
    class="min-h-screen"
>
    <div class="fixed inset-0 z-40 bg-slate-900/40 transition lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false; guestPrefsOpen = false"></div>

    <x-app.sidebar
        :logo-url="$sidebarLogoUrl"
        :brand-name="$brandName"
        :categories="$categories"
        :display-name="$displayName"
        :user-initial="$userInitial"
        :is-authenticated="$isAuthenticated"
    />

    <div class="lg:pl-16">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
            <div class="mx-auto flex w-full max-w-[1320px] flex-wrap items-center gap-2.5 px-4 py-3 sm:gap-3 sm:px-6 sm:py-4">
                <button class="order-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm lg:hidden" type="button" @click="sidebarOpen = true" aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="7" x2="20" y2="7" />
                        <line x1="4" y1="12" x2="20" y2="12" />
                        <line x1="4" y1="17" x2="20" y2="17" />
                    </svg>
                </button>

                <form
                    id="global-catalog-search-form"
                    method="GET"
                    action="{{ route('catalog') }}"
                    data-search-suggest-url="{{ route('search.suggestions') }}"
                    class="relative order-3 w-full sm:order-2 sm:flex-1 sm:max-w-2xl"
                >
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.5 3.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10ZM2 8.5a6.5 6.5 0 1 1 11.158 4.157l3.092 3.093a1 1 0 0 1-1.414 1.414l-3.093-3.092A6.5 6.5 0 0 1 2 8.5Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input
                        id="global-catalog-search-input"
                        type="text"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="{{ __('ui.nav.search_placeholder') }}"
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                    >
                    <div
                        id="global-catalog-search-dropdown"
                        class="absolute left-0 right-0 top-[calc(100%+0.45rem)] z-50 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                    ></div>
                </form>

                <div class="order-2 ml-auto flex items-center gap-2 sm:order-3 sm:gap-3">
                    @if ($isAuthenticated)
                        <div class="relative" @click.outside="notifOpen = false">
                            <button
                                type="button"
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                                @click="notifOpen = !notifOpen"
                                :aria-expanded="notifOpen.toString()"
                                aria-label="{{ __('ui.nav.notifications') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                    <path d="M9 17a3 3 0 0 0 6 0" />
                                </svg>
                                <span
                                    x-cloak
                                    x-show="notifCount > 0"
                                    x-text="notifCount"
                                    class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-semibold text-white"
                                ></span>
                            </button>
                            <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="card absolute right-0 mt-2 w-80 rounded-xl p-3 shadow-lg">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('ui.nav.notifications') }}</p>
                                <div class="mt-2 max-h-72 space-y-2 overflow-y-auto">
                                    @forelse ($notificationItems as $notification)
                                        @php
                                            $notificationTargetUrl = $notification['url'] ?? route('booking.history');
                                            $notificationReadUrl = $notification['mark_read_url'] ?? '';
                                            $notificationUnread = ! empty($notification['is_new']);
                                        @endphp
                                        <a
                                            href="{{ $notificationTargetUrl }}"
                                            data-read="{{ $notificationUnread ? '0' : '1' }}"
                                            class="block rounded-xl border border-slate-200 px-3 py-2 hover:border-blue-200"
                                            @click.prevent="openNotification($event, '{{ $notificationTargetUrl }}', '{{ $notificationReadUrl }}', {{ $notificationUnread ? 'true' : 'false' }})"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs font-semibold text-slate-800">{{ $notification['title'] }}</p>
                                                @if (!empty($notification['is_new']))
                                                    <span class="mt-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-slate-600">{{ $notification['body'] }}</p>
                                            @if (!empty($notification['time']))
                                                <p class="mt-1 text-[11px] text-slate-400">{{ $notification['time'] }}</p>
                                            @endif
                                        </a>
                                    @empty
                                        <p class="rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-500">{{ __('app.notifications.empty') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                            aria-label="{{ __('ui.nav.notifications') }}"
                            title="{{ __('ui.nav.notifications') }}"
                            @click="openAuthModal('login')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                <path d="M9 17a3 3 0 0 0 6 0" />
                            </svg>
                        </button>
                    @endif

                    @if ($isAuthenticated)
                        <a
                            href="{{ route('cart') }}"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                            aria-label="{{ __('ui.nav.cart') }}"
                            title="{{ __('ui.nav.cart') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                            </svg>
                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-semibold text-white">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    @else
                        <button
                            type="button"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                            aria-label="{{ __('ui.nav.cart') }}"
                            title="{{ __('ui.nav.cart') }}"
                            @click="openAuthModal('login')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                            </svg>
                        </button>
                    @endif

                    @unless($isAuthenticated)
                        <button
                            type="button"
                            class="hidden text-sm font-semibold text-slate-600 transition hover:text-blue-600 sm:inline"
                            @click="openAuthModal('login')"
                        >
                            {{ __('ui.nav.login') }}
                        </button>
                        <button
                            type="button"
                            class="hidden rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 sm:inline"
                            @click="openAuthModal('register')"
                        >
                            {{ __('ui.nav.register') }}
                        </button>
                    @endunless
                </div>
            </div>
        </header>

        <main class="px-4 py-5 sm:px-6 sm:py-8">
            <div class="mx-auto w-full max-w-[1320px]">
                @yield('content')
            </div>
        </main>

        @include('partials.footer')
    </div>

    @unless($isAuthenticated)
        <div
            x-cloak
            x-show="authModalOpen"
            class="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6"
            @keydown.escape.window="closeAuthModal()"
        >
            <div class="absolute inset-0 bg-slate-900/55" @click="closeAuthModal()"></div>

            <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
                <button
                    type="button"
                    class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                    @click="closeAuthModal()"
                    aria-label="{{ __('ui.actions.close') }}"
                >
                    ✕
                </button>

                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                        <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition" :class="authModalView === 'login' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500'" @click="authModalView = 'login'">{{ __('ui.nav.login') }}</button>
                        <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition" :class="authModalView === 'register' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500'" @click="authModalView = 'register'">{{ __('ui.nav.register') }}</button>
                        <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition" :class="authModalView === 'forgot' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500'" @click="authModalView = 'forgot'">{{ __('ui.auth.forgot_title') }}</button>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'login'" x-transition>
                        <h2 class="text-2xl font-semibold text-slate-900">{{ __('app.auth.login_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ __('app.auth.login_subheading') }}</p>

                        @if (session('error') && (old('auth_modal') === 'login' || $authModalView === 'login'))
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any() && old('auth_modal') === 'login')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="login">

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.email') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'login' ? old('email') : '' }}"
                                    required
                                    class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    placeholder="{{ __('app.auth.email_placeholder') }}"
                                >
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.password') }}</label>
                                <x-password-input
                                    id="modal-login-password"
                                    name="password"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_placeholder_mask') }}"
                                    autocomplete="current-password"
                                    wrapper-class="mt-2"
                                    input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                />
                            </div>

                            <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                {{ __('app.auth.login_button') }}
                            </button>
                        </form>

                        <button type="button" class="mt-3 text-sm font-semibold text-blue-600 hover:text-blue-700" @click="authModalView = 'forgot'">
                            {{ __('app.auth.forgot_password') }}
                        </button>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'register'" x-transition>
                        <h2 class="text-2xl font-semibold text-slate-900">{{ __('app.auth.register_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ __('app.auth.register_subheading') }}</p>

                        @if ($errors->any() && old('auth_modal') === 'register')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="register">

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.email') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'register' ? old('email') : '' }}"
                                    required
                                    class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    placeholder="{{ __('app.auth.email_placeholder') }}"
                                >
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.password') }}</label>
                                <x-password-input
                                    id="modal-register-password"
                                    name="password"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_placeholder') }}"
                                    autocomplete="new-password"
                                    wrapper-class="mt-2"
                                    input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.password_confirm') }}</label>
                                <x-password-input
                                    id="modal-register-password-confirmation"
                                    name="password_confirmation"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_confirm_placeholder') }}"
                                    autocomplete="new-password"
                                    wrapper-class="mt-2"
                                    input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                />
                            </div>

                            <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                {{ __('app.auth.register_button') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'forgot'" x-transition>
                        <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.auth.forgot_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ __('ui.auth.forgot_subheading') }}</p>

                        @if (session('status') && ($authModalView === 'forgot' || old('auth_modal') === 'forgot'))
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any() && old('auth_modal') === 'forgot')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="forgot">

                            <div>
                                <label class="text-xs font-semibold text-slate-500">{{ __('ui.auth.email_label') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'forgot' ? old('email') : '' }}"
                                    required
                                    class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                    placeholder="{{ __('ui.auth.email_placeholder') }}"
                                >
                            </div>

                            <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                {{ __('ui.auth.forgot_button') }}
                            </button>
                        </form>

                        <button type="button" class="mt-3 text-sm font-semibold text-blue-600 hover:text-blue-700" @click="authModalView = 'login'">
                            {{ __('ui.auth.back_to_login') }}
                        </button>
                    </div>
                </div>

                <div class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-blue-900 to-slate-900 p-8 text-white lg:block lg:p-10">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top,_white,_transparent_60%)]"></div>
                    <div class="relative z-10">
                        <img src="{{ asset('manake-logo-blue.png') }}" alt="Manake" class="h-12 w-auto rounded-xl bg-white p-2">
                        <h1 class="mt-6 text-3xl font-semibold leading-tight">{{ __('app.auth.login_heading') }}</h1>
                        <p class="mt-4 text-sm leading-relaxed text-blue-100">
                            {{ __('app.auth.login_note') }}
                        </p>
                        <div class="mt-7 space-y-3 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                                <p>{{ __('app.auth.login_benefit_1') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                                <p>{{ __('app.auth.login_benefit_2') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                                <p>{{ __('app.auth.login_benefit_3') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endunless
</div>

@include('partials.ui-feedback')
@stack('scripts')
@include('partials.theme-toggle')
<script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    if (window.axios) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
    }
    window.fetchWithCsrf = (url, options = {}) => {
        const headers = new Headers(options.headers || {});
        headers.set('X-CSRF-TOKEN', window.csrfToken);
        return fetch(url, { ...options, headers });
    };

    (function () {
        const form = document.getElementById('global-catalog-search-form');
        const input = document.getElementById('global-catalog-search-input');
        const dropdown = document.getElementById('global-catalog-search-dropdown');
        if (!form || !input || !dropdown) {
            return;
        }

        const endpoint = form.dataset.searchSuggestUrl || '';
        const minimumQueryLength = 2;
        let debounceTimer = null;
        let activeAbortController = null;
        let lastItems = [];

        const formatRupiah = (value) => {
            const amount = Number(value || 0);
            return `Rp ${amount.toLocaleString('id-ID')}`;
        };

        const hideDropdown = () => {
            dropdown.classList.add('hidden');
        };

        const showDropdown = () => {
            dropdown.classList.remove('hidden');
        };

        const createItemNode = (item) => {
            const link = document.createElement('a');
            link.href = item.detail_url || '#';
            link.className = 'flex items-center gap-3 border-b border-slate-100 px-3 py-2.5 transition hover:bg-blue-50';

            const image = document.createElement('img');
            image.src = item.image_url || '{{ asset('MANAKE-FAV-M.png') }}';
            image.alt = item.name || 'Alat';
            image.loading = 'lazy';
            image.className = 'h-12 w-12 rounded-lg border border-slate-200 bg-slate-50 object-cover';
            link.appendChild(image);

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const name = document.createElement('p');
            name.className = 'truncate text-sm font-semibold text-slate-900';
            name.textContent = item.name || 'Alat';
            content.appendChild(name);

            const meta = document.createElement('p');
            meta.className = 'mt-0.5 truncate text-xs italic text-slate-500';
            meta.textContent = item.overview || item.category || '';
            content.appendChild(meta);

            const pricing = document.createElement('p');
            pricing.className = 'mt-0.5 text-[11px] font-medium text-blue-700';
            pricing.textContent = `${formatRupiah(item.price_per_day)} / hari`;
            content.appendChild(pricing);

            link.appendChild(content);

            const stockBadge = document.createElement('span');
            stockBadge.className = 'rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700';
            stockBadge.textContent = `sisa ${Math.max(Number(item.available_units || 0), 0)}`;
            link.appendChild(stockBadge);

            return link;
        };

        const renderDropdown = (items, query) => {
            dropdown.innerHTML = '';
            lastItems = items;

            const list = document.createElement('div');
            list.className = 'max-h-[22rem] overflow-y-auto';

            if (!items.length) {
                const emptyState = document.createElement('p');
                emptyState.className = 'px-3 py-3 text-xs text-slate-500';
                emptyState.textContent = `Tidak ada hasil untuk "${query}".`;
                list.appendChild(emptyState);
            } else {
                items.forEach((item) => {
                    list.appendChild(createItemNode(item));
                });
            }

            dropdown.appendChild(list);

            const footer = document.createElement('a');
            footer.href = `{{ route('catalog') }}?q=${encodeURIComponent(query)}`;
            footer.className = 'block border-t border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50';
            footer.textContent = `Telusuri hasil "${query}"`;
            dropdown.appendChild(footer);

            showDropdown();
        };

        const fetchSuggestions = async (query) => {
            if (!endpoint) {
                return [];
            }

            if (activeAbortController) {
                activeAbortController.abort();
            }
            activeAbortController = new AbortController();

            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('q', query);

                const response = await fetch(url.toString(), {
                    headers: { Accept: 'application/json' },
                    signal: activeAbortController.signal,
                });
                if (!response.ok) {
                    return [];
                }

                const payload = await response.json().catch(() => ({}));
                return Array.isArray(payload.data) ? payload.data : [];
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return null;
                }
                return [];
            }
        };

        input.addEventListener('input', () => {
            const query = input.value.trim();

            if (query.length < minimumQueryLength) {
                hideDropdown();
                dropdown.innerHTML = '';
                lastItems = [];
                return;
            }

            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            debounceTimer = window.setTimeout(async () => {
                const activeQuery = input.value.trim();
                if (activeQuery.length < minimumQueryLength) {
                    hideDropdown();
                    return;
                }

                const items = await fetchSuggestions(activeQuery);
                if (items === null) {
                    return;
                }
                if (input.value.trim() !== activeQuery) {
                    return;
                }

                renderDropdown(items, activeQuery);
            }, 220);
        });

        input.addEventListener('focus', () => {
            if (lastItems.length > 0 && input.value.trim().length >= minimumQueryLength) {
                showDropdown();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hideDropdown();
            }
        });

        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) {
                hideDropdown();
            }
        });
    })();
</script>

</body>
</html>

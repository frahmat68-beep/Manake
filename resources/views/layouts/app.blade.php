@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get(
        'theme_resolved',
        $initialThemePreference === 'dark' ? 'dark' : 'light'
    );
@endphp
<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    class="scroll-smooth {{ $initialThemeResolved === 'dark' ? 'dark' : '' }}"
    data-theme="manake-brand"
    data-theme-preference="{{ $initialThemePreference }}"
    data-theme-resolved="{{ $initialThemeResolved }}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', site_setting('seo.meta_description', setting('meta_description', 'Manake Rental menyediakan rental alat produksi profesional: kamera, lighting, drone, dan audio.')))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', site_setting('seo.meta_title', setting('meta_title', setting('site_name', 'Manake.Id'))))</title>
    @php
        $assetWithVersion = static function (string $file): string {
            return site_asset($file);
        };
        $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $brandLogoUrl = $assetWithVersion('manake-logo-blue.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
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
            color: var(--manake-body-color-resolved, var(--text)) !important;
            font-weight: var(--manake-body-weight);
            font-style: var(--manake-body-style);
            font-size: calc(1rem * var(--manake-body-scale));
        }
        :root {
            --manake-heading-h1-light: {{ $headingColor }};
            --manake-heading-h2-light: {{ $subheadingColor }};
            --manake-heading-h3-light: {{ $subheadingColor }};
            --manake-heading-h4-light: {{ $headingColor }};
            --manake-body-color-light: {{ $bodyColor }};
            --manake-heading-h1-dark: color-mix(in oklab, {{ $headingColor }} 72%, white 28%);
            --manake-heading-h2-dark: color-mix(in oklab, {{ $subheadingColor }} 70%, white 30%);
            --manake-heading-h3-dark: color-mix(in oklab, {{ $subheadingColor }} 70%, white 30%);
            --manake-heading-h4-dark: color-mix(in oklab, {{ $headingColor }} 74%, white 26%);
            --manake-body-color-dark: color-mix(in oklab, var(--text) 90%, white 10%);
            --manake-heading-h1-resolved: var(--manake-heading-h1-light);
            --manake-heading-h2-resolved: var(--manake-heading-h2-light);
            --manake-heading-h3-resolved: var(--manake-heading-h3-light);
            --manake-heading-h4-resolved: var(--manake-heading-h4-light);
            --manake-body-color-resolved: var(--manake-body-color-light);
            --manake-heading-weight: {{ $headingWeight }};
            --manake-body-weight: {{ $bodyWeight }};
            --manake-heading-style: {{ $headingStyle }};
            --manake-body-style: {{ $bodyStyle }};
            --manake-heading-scale: {{ $headingScaleMap[$headingScaleKey] ?? '1' }};
            --manake-body-scale: {{ $bodyScaleMap[$bodyScaleKey] ?? '1' }};
        }
        html[data-theme-resolved='dark'] {
            --manake-heading-h1-resolved: var(--manake-heading-h1-dark);
            --manake-heading-h2-resolved: var(--manake-heading-h2-dark);
            --manake-heading-h3-resolved: var(--manake-heading-h3-dark);
            --manake-heading-h4-resolved: var(--manake-heading-h4-dark);
            --manake-body-color-resolved: var(--manake-body-color-dark);
        }
        header :is(h1, h2, h3) {
            color: var(--manake-heading-h1-resolved) !important;
            letter-spacing: -0.012em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
        }
        main h1 {
            color: var(--manake-heading-h1-resolved) !important;
            letter-spacing: -0.015em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(2rem * var(--manake-heading-scale)) !important;
        }
        main h2 {
            color: var(--manake-heading-h2-resolved) !important;
            letter-spacing: -0.012em;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(1.5rem * var(--manake-heading-scale)) !important;
        }
        main h3 {
            color: var(--manake-heading-h3-resolved) !important;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
            font-size: calc(1.125rem * var(--manake-heading-scale)) !important;
        }
        main :is(h4, h5, h6) {
            color: var(--manake-heading-h4-resolved) !important;
            font-style: var(--manake-heading-style) !important;
            font-weight: var(--manake-heading-weight) !important;
        }
        table tbody tr:hover td {
            background-color: #eaf2ff;
            color: #0f172a;
        }
        table tbody tr:focus-within td {
            background-color: #eaf2ff;
            color: #0f172a;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800" data-manake-shell="app">
@include('partials.page-loader')
@php
    $isAuthenticated = auth('web')->check();
    $brandName = site_setting('brand.name', 'Manake');
    $sidebarLogoUrl = $brandLogoUrl;
    $locale = app()->getLocale();

    $categories = collect($navCategories ?? [])->filter(fn ($category) => ! empty($category->slug ?? null))->values();
    $notificationItems = collect($notificationItems ?? []);

    $searchQuery = trim((string) request('q', ''));
    $displayName = auth('web')->user()->display_name ?? auth('web')->user()->name ?? __('app.user.generic');
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
        notifBadge() {
            return this.notifCount > 99 ? '99+' : String(this.notifCount);
        },
        shellPrefsOpen: false,
        authModalOpen: {{ $authModalOpen ? 'true' : 'false' }},
        authModalView: '{{ $authModalView ?: 'login' }}',
        openAuthModal(view = 'login') {
            this.authModalView = view;
            this.authModalOpen = true;
            this.notifOpen = false;
            this.shellPrefsOpen = false;
            this.sidebarOpen = false;
        },
        closeAuthModal() {
            this.authModalOpen = false;
        },
        async openNotification(event, targetUrl, markReadUrl, isUnread = false) {
            const navigateTo = targetUrl || '{{ route('notifications') }}';

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
    <div class="fixed inset-0 z-40 bg-slate-900/40 transition lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false; shellPrefsOpen = false"></div>

    <x-app.sidebar
        :logo-url="$sidebarLogoUrl"
        :brand-name="$brandName"
        :categories="$categories"
        :display-name="$displayName"
        :user-initial="$userInitial"
        :is-authenticated="$isAuthenticated"
    />

    <div class="lg:pl-24">
        <header class="manake-topbar-shell sticky top-0 z-30 border-b border-slate-200 bg-white" data-manake-topbar="app">
            <div class="mx-auto flex w-full max-w-[1320px] flex-wrap items-center gap-2 px-4 py-2.5 sm:gap-3 sm:px-6 sm:py-3">
                <button data-ui-icon-button class="order-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl lg:hidden" type="button" @click="sidebarOpen = true" aria-label="{{ __('ui.nav.toggle_menu') }}">
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
                    class="command-surface command-surface--search relative order-3 w-full rounded-2xl sm:order-2 sm:flex-1 sm:max-w-xl"
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
                        class="w-full rounded-2xl border-0 bg-transparent py-3 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-transparent focus:outline-none focus:ring-0"
                    >
                    <div
                        id="global-catalog-search-dropdown"
                        class="absolute left-0 right-auto top-[calc(100%+0.45rem)] z-50 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                    ></div>
                </form>

                <div class="order-2 ml-auto flex items-center gap-2 sm:order-3 sm:gap-3">
                    @if ($isAuthenticated)
                        <div class="relative" @click.outside="notifOpen = false">
                            <button
                                type="button"
                                data-ui-icon-button
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
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
                                    x-text="notifBadge()"
                                    class="absolute -right-1.5 -top-1.5 inline-flex h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-blue-600 px-1.5 text-[10px] font-semibold leading-none text-white"
                                ></span>
                            </button>
                            <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="card absolute right-0 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] rounded-xl p-3 shadow-lg">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('ui.nav.notifications') }}</p>
                                <div class="mt-2 max-h-72 space-y-2 overflow-y-auto">
                                    @forelse ($notificationItems as $notification)
                                        @php
                                            $notificationTargetUrl = $notification['url'] ?? route('notifications');
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
                                <a href="{{ route('notifications') }}" class="mt-3 block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-center text-xs font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-50" @click="notifOpen = false">
                                    {{ __('ui.nav.view_all') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <button
                            type="button"
                            data-ui-icon-button
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
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
                            data-ui-icon-button
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
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
                                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                                </span>
                            @endif
                        </a>
                    @else
                        <button
                            type="button"
                            data-ui-icon-button
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition"
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
                            data-ui-text-button
                            class="hidden text-sm font-semibold transition sm:inline"
                            @click="openAuthModal('login')"
                        >
                            {{ __('ui.nav.login') }}
                        </button>
                        <button
                            type="button"
                            class="btn-primary hidden rounded-xl px-3 py-2 text-sm font-semibold transition sm:inline"
                            @click="openAuthModal('register')"
                        >
                            {{ __('ui.nav.register') }}
                        </button>
                    @endunless
                </div>
            </div>
        </header>

        <main class="manake-main-stage px-4 py-3 sm:px-6 sm:py-4">
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

            <div class="manake-auth-card relative z-10 w-full max-w-5xl overflow-hidden rounded-[2rem] lg:grid lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <button
                    type="button"
                    data-ui-icon-button
                    class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full transition"
                    @click="closeAuthModal()"
                    aria-label="{{ __('ui.actions.close') }}"
                >
                    ✕
                </button>

                <div class="manake-auth-panel p-6 sm:p-8 lg:p-10">
                    <a href="{{ route('home') }}" class="inline-flex items-center" data-skip-loader="true">
                        <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" alt="Manake" img-class="h-10 w-auto" />
                    </a>

                    <div class="mt-5 inline-flex rounded-2xl border border-slate-200 bg-slate-50/90 p-1">
                        <button type="button" data-ui-chip-option :data-ui-active="authModalView === 'login' ? 'true' : 'false'" class="rounded-xl px-3 py-2 text-xs font-semibold" @click="authModalView = 'login'">{{ __('ui.nav.login') }}</button>
                        <button type="button" data-ui-chip-option :data-ui-active="authModalView === 'register' ? 'true' : 'false'" class="rounded-xl px-3 py-2 text-xs font-semibold" @click="authModalView = 'register'">{{ __('ui.nav.register') }}</button>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'login'" x-transition>
                        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-slate-950">{{ __('app.auth.login_title') }}</h2>

                        @if (session('error') && (old('auth_modal') === 'login' || $authModalView === 'login'))
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any() && old('auth_modal') === 'login')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="login">

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('app.auth.email') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'login' ? old('email') : '' }}"
                                    required
                                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                                    placeholder="{{ __('app.auth.email_placeholder') }}"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between gap-3">
                                    <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('app.auth.password') }}</label>
                                    <button type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700" @click="authModalView = 'forgot'">
                                        {{ __('app.auth.forgot_password') }}
                                    </button>
                                </div>
                                <x-password-input
                                    id="modal-login-password"
                                    name="password"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_placeholder_mask') }}"
                                    autocomplete="current-password"
                                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                                />
                            </div>

                            <button class="btn-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                {{ __('app.auth.login_button') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'register'" x-transition>
                        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-slate-950">{{ __('app.auth.register_title') }}</h2>

                        @if ($errors->any() && old('auth_modal') === 'register')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="register">

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('app.auth.email') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'register' ? old('email') : '' }}"
                                    required
                                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                                    placeholder="{{ __('app.auth.email_placeholder') }}"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('app.auth.password') }}</label>
                                <x-password-input
                                    id="modal-register-password"
                                    name="password"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_placeholder') }}"
                                    autocomplete="new-password"
                                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('app.auth.password_confirm') }}</label>
                                <x-password-input
                                    id="modal-register-password-confirmation"
                                    name="password_confirmation"
                                    :required="true"
                                    placeholder="{{ __('app.auth.password_confirm_placeholder') }}"
                                    autocomplete="new-password"
                                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                                />
                            </div>

                            <button class="btn-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                {{ __('app.auth.register_button') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-6" x-show="authModalView === 'forgot'" x-transition>
                        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-slate-950">{{ __('ui.auth.forgot_title') }}</h2>

                        @if (session('status') && ($authModalView === 'forgot' || old('auth_modal') === 'forgot'))
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any() && old('auth_modal') === 'forgot')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="auth_modal" value="forgot">

                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('ui.auth.email_label') }}</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'forgot' ? old('email') : '' }}"
                                    required
                                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                                    placeholder="{{ __('ui.auth.email_placeholder') }}"
                                >
                            </div>

                            <button class="btn-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                {{ __('ui.auth.forgot_button') }}
                            </button>
                        </form>

                        <button type="button" class="mt-3 text-sm font-semibold text-blue-600 hover:text-blue-700" @click="authModalView = 'login'">
                            {{ __('ui.auth.back_to_login') }}
                        </button>
                    </div>
                </div>

                <div class="manake-auth-showcase relative hidden overflow-hidden p-8 text-white lg:flex lg:flex-col lg:justify-between lg:p-10">
                    <div class="relative z-10 space-y-5">
                        <x-brand.image
                            light="manake-logo-white.png"
                            dark="manake-logo-white.png"
                            alt="Manake"
                            img-class="h-[2.6rem] w-auto"
                        />
                        <p class="max-w-sm text-sm leading-6 text-blue-100/82">
                            {{ __('app.auth.login_benefit_1') }}
                        </p>
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
        const genericItemLabel = @json(__('ui.nav.search_generic_item'));
        const minimumQueryLength = 2;
        const maxDropdownItems = 4;
        let debounceTimer = null;
        let activeAbortController = null;
        let lastItems = [];

        const hideDropdown = () => {
            dropdown.classList.add('hidden');
        };

            const showDropdown = () => {
                dropdown.style.width = `min(${form.getBoundingClientRect().width}px, 24rem)`;
                dropdown.classList.remove('hidden');
            };

        const createItemNode = (item) => {
            const link = document.createElement('a');
            link.href = item.detail_url || '#';
            link.className = 'flex items-center gap-3 px-3 py-2 transition hover:bg-blue-50';

            const image = document.createElement('img');
            image.src = item.image_url || '{{ site_asset('MANAKE-FAV-M.png') }}';
            image.alt = item.name || genericItemLabel;
            image.loading = 'lazy';
            image.className = 'h-9 w-9 rounded-xl border border-slate-200 bg-slate-50 object-cover';
            link.appendChild(image);

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const name = document.createElement('p');
            name.className = 'truncate text-[0.92rem] font-semibold text-slate-900';
            name.textContent = item.name || genericItemLabel;
            content.appendChild(name);

            link.appendChild(content);

            return link;
        };

        const renderDropdown = (items) => {
            dropdown.innerHTML = '';
            lastItems = items.slice(0, maxDropdownItems);

            if (!lastItems.length) {
                hideDropdown();
                return;
            }

            const list = document.createElement('div');
            list.className = 'scroll-panel max-h-[14rem] divide-y divide-slate-100 overflow-y-auto';

            lastItems.forEach((item) => {
                list.appendChild(createItemNode(item));
            });

            dropdown.appendChild(list);

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

                renderDropdown(items);
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

    <x-chatbot-widget />
</body>
</html>

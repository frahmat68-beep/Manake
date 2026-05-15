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
        [data-manake-shell="app"] {
            overflow-x: hidden;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased selection:bg-blue-600/10 selection:text-blue-600" data-manake-shell="app">
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
    x-on:mousemove="
        const shell = $el.closest('[data-manake-shell]');
        if (shell) {
            shell.style.setProperty('--x', $event.clientX + 'px');
            shell.style.setProperty('--y', $event.clientY + 'px');
        }
    "
    class="min-h-screen"
>
    <div class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm transition lg:hidden" x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false; shellPrefsOpen = false"></div>

    <x-app.sidebar
        :logo-url="$sidebarLogoUrl"
        :brand-name="$brandName"
        :categories="$categories"
        :display-name="$displayName"
        :user-initial="$userInitial"
        :is-authenticated="$isAuthenticated"
    />

    <div class="lg:pl-24">
        <header class="manake-topbar-shell glass-lg sticky top-0 z-30 border-b border-slate-200/50" data-manake-topbar="app">
            <div class="mx-auto flex w-full max-w-[1320px] flex-wrap items-center gap-2 px-4 py-2.5 sm:gap-3 sm:px-6 sm:py-3">
                <button data-ui-icon-button class="order-1 hover-scale inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl lg:hidden transition-all hover:bg-slate-100" type="button" @click="sidebarOpen = true" aria-label="{{ __('ui.nav.toggle_menu') }}">
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
                    class="command-surface command-surface--search relative order-3 w-full rounded-2xl sm:order-2 sm:flex-1 sm:max-w-xl transition-all focus-within:ring-4 focus-within:ring-blue-600/10 focus-within:border-blue-500/50"
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
                        class="w-full rounded-2xl border-0 bg-transparent py-3 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                    >
                    <div
                        id="global-catalog-search-dropdown"
                        class="absolute left-0 right-auto top-[calc(100%+0.6rem)] z-50 hidden overflow-hidden rounded-2xl border border-slate-200/50 glass-lg shadow-2xl animate-fade-up"
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
                                    class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-slate-900"
                                ></span>
                            </button>
                            <div x-cloak x-show="notifOpen" x-transition.origin.top.right class="card absolute right-0 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] rounded-2xl p-4 shadow-2xl glass-lg">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-600">{{ __('ui.nav.notifications') }}</p>
                                <div class="mt-3 max-h-72 space-y-2 overflow-y-auto pr-1">
                                    @forelse ($notificationItems as $notification)
                                        @php
                                            $notificationTargetUrl = $notification['url'] ?? route('notifications');
                                            $notificationReadUrl = $notification['mark_read_url'] ?? '';
                                            $notificationUnread = ! empty($notification['is_new']);
                                        @endphp
                                        <a
                                            href="{{ $notificationTargetUrl }}"
                                            data-read="{{ $notificationUnread ? '0' : '1' }}"
                                            class="block rounded-xl border border-slate-200/50 p-3 transition hover:border-blue-400/50 hover:bg-blue-50/50"
                                            @click.prevent="openNotification($event, '{{ $notificationTargetUrl }}', '{{ $notificationReadUrl }}', {{ $notificationUnread ? 'true' : 'false' }})"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-xs font-bold text-slate-900">{{ $notification['title'] }}</p>
                                                @if (!empty($notification['is_new']))
                                                    <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-slate-600 line-clamp-2">{{ $notification['body'] }}</p>
                                            @if (!empty($notification['time']))
                                                <p class="mt-2 text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ $notification['time'] }}</p>
                                            @endif
                                        </a>
                                    @empty
                                        <div class="flex flex-col items-center justify-center py-8 text-center">
                                            <div class="rounded-full bg-slate-50 p-3 text-slate-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-xs font-medium text-slate-400">{{ __('app.notifications.empty') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications') }}" class="mt-4 block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-center text-xs font-bold text-blue-600 transition hover:bg-blue-50 hover:text-blue-700" @click="notifOpen = false">
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
                                <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">
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
                            class="hidden text-sm font-bold tracking-tight transition sm:inline hover:text-blue-600"
                            @click="openAuthModal('login')"
                        >
                            {{ __('ui.nav.login') }}
                        </button>
                        <button
                            type="button"
                            class="btn-primary hidden rounded-xl px-4 py-2 text-sm font-bold transition sm:inline"
                            @click="openAuthModal('register')"
                        >
                            {{ __('ui.nav.register') }}
                        </button>
                    @endunless
                </div>
            </div>
        </header>

        <main class="manake-main-stage px-4 py-6 sm:px-8 sm:py-8">
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
            class="fixed inset-0 z-[80] min-h-screen flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-xl overflow-hidden w-full p-4"
            @keydown.escape.window="closeAuthModal()"
        >
            <div class="absolute inset-0" @click="closeAuthModal()"></div>

            <button
                type="button"
                class="absolute right-6 top-6 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 active:scale-90"
                @click="closeAuthModal()"
                aria-label="{{ __('ui.actions.close') }}"
            >
                ✕
            </button>
            
            <div class="relative z-10 w-full max-w-sm rounded-[2.5rem] border border-white/10 bg-slate-900/90 p-8 shadow-[0_0_100px_-20px_rgba(37,99,235,0.4)] animate-fade-up">
                <!-- Logo -->
                <div class="mb-8 flex justify-center">
                    <img src="{{ asset('manake-logo-blue.png') }}" alt="Manake" class="h-16 w-auto drop-shadow-[0_0_15px_rgba(37,99,235,0.4)]" />
                </div>
                
                <div class="flex flex-col w-full">
                    <!-- Global Errors/Status -->
                    @if (session('error') || session('status') || $errors->any())
                        <div class="mb-4 flex flex-col gap-2">
                            @if (session('error'))
                                <p class="text-center text-xs font-medium text-red-400">{{ session('error') }}</p>
                            @endif
                            @if (session('status'))
                                <p class="text-center text-xs font-medium text-emerald-400">{{ session('status') }}</p>
                            @endif
                            @if ($errors->any())
                                <p class="text-center text-xs font-medium text-red-400">{{ $errors->first() }}</p>
                            @endif
                        </div>
                    @endif
                    
                    <div class="w-full">
                        <!-- LOGIN FORM -->
                        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4" x-show="authModalView === 'login'" x-transition.opacity.duration.300ms>
                            @csrf
                            <input type="hidden" name="auth_modal" value="login">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'login' ? old('email') : '' }}"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="Enter your email"
                                />
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between ml-1">
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Password</label>
                                    <button type="button" class="text-[10px] font-bold uppercase tracking-widest text-blue-500 hover:text-blue-400 transition" @click="authModalView = 'forgot'">
                                        Forgot?
                                    </button>
                                </div>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="••••••••"
                                />
                            </div>
                            
                            <button type="submit" class="btn-primary mt-2 flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-bold shadow-blue-900/20">
                                Sign In
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <div class="relative my-4 flex items-center justify-center">
                                <span class="absolute h-[1px] w-full bg-white/5"></span>
                                <span class="relative bg-slate-900 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">OR</span>
                            </div>
                            
                            <x-auth.google-button label="Continue with Google" class="flex w-full items-center justify-center gap-3 rounded-2xl border border-white/10 bg-white/5 py-3.5 text-sm font-bold text-white transition hover:bg-white/10 active:scale-95" />
                            
                            <p class="mt-4 text-center text-xs text-slate-400">
                                Don't have an account? 
                                <button type="button" class="font-bold text-blue-500 hover:text-blue-400" @click="authModalView = 'register'">
                                    Create one now
                                </button>
                            </p>
                        </form>

                        <!-- REGISTER FORM -->
                        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4" x-show="authModalView === 'register'" x-cloak x-transition.opacity.duration.300ms>
                            @csrf
                            <input type="hidden" name="auth_modal" value="register">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'register' ? old('email') : '' }}"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="Enter your email"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="••••••••"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Confirm Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="••••••••"
                                />
                            </div>
                            
                            <button type="submit" class="btn-primary mt-2 flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-bold shadow-blue-900/20">
                                Create Account
                            </button>
                            
                            <p class="mt-4 text-center text-xs text-slate-400">
                                Already have an account? 
                                <button type="button" class="font-bold text-blue-500 hover:text-blue-400" @click="authModalView = 'login'">
                                    Sign In
                                </button>
                            </p>
                        </form>

                        <!-- FORGOT PASSWORD FORM -->
                        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4" x-show="authModalView === 'forgot'" x-cloak x-transition.opacity.duration.300ms>
                            @csrf
                            <input type="hidden" name="auth_modal" value="forgot">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('auth_modal') === 'forgot' ? old('email') : '' }}"
                                    required
                                    class="w-full rounded-2xl border border-white/5 bg-white/5 px-5 py-3.5 text-sm text-white placeholder:text-slate-600 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-600/20 transition-all"
                                    placeholder="Enter your email"
                                />
                            </div>
                            
                            <button type="submit" class="btn-primary mt-2 flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-bold shadow-blue-900/20">
                                Send Reset Link
                            </button>
                            
                            <button type="button" class="mt-4 text-center text-xs font-bold text-slate-400 hover:text-white transition" @click="authModalView = 'login'">
                                ← Back to Login
                            </button>
                        </form>
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
                dropdown.style.width = `min(${form.getBoundingClientRect().width}px, 32rem)`;
                dropdown.classList.remove('hidden');
            };

        const createItemNode = (item) => {
            const link = document.createElement('a');
            link.href = item.detail_url || '#';
            link.className = 'flex items-center gap-4 px-4 py-3 transition hover:bg-blue-50/50 group';

            const image = document.createElement('img');
            image.src = item.image_url || '{{ site_asset('MANAKE-FAV-M.png') }}';
            image.alt = item.name || genericItemLabel;
            image.loading = 'lazy';
            image.className = 'h-12 w-12 rounded-2xl border border-slate-200 bg-slate-50 object-cover group-hover:scale-105 transition-transform duration-300';
            link.appendChild(image);

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const name = document.createElement('p');
            name.className = 'truncate text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors';
            name.textContent = item.name || genericItemLabel;
            content.appendChild(name);
            
            if (item.category) {
                const cat = document.createElement('p');
                cat.className = 'text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5';
                cat.textContent = item.category;
                content.appendChild(cat);
            }

            link.appendChild(content);
            
            const arrow = document.createElement('span');
            arrow.className = 'text-slate-300 group-hover:text-blue-500 transition-colors opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all';
            arrow.innerHTML = '→';
            link.appendChild(arrow);

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
            list.className = 'scroll-panel max-h-[22rem] divide-y divide-slate-100/50 overflow-y-auto';

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

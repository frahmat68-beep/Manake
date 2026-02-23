<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.admin.panel_title') . ' | Manake')</title>
    @php
        $assetWithVersion = static function (string $file): string {
            $path = public_path($file);
            $version = file_exists($path) ? (string) filemtime($path) : '1';
            return asset($file) . '?v=' . $version;
        };
        $faviconLightUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $faviconDarkUrl = $assetWithVersion('MANAKE-FAV-M-white.png');
    @endphp
    <link
        rel="icon"
        type="image/png"
        href="{{ $faviconLightUrl }}"
        data-theme-favicon
        data-light="{{ $faviconLightUrl }}"
        data-dark="{{ $faviconDarkUrl }}"
    >
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
<body class="min-h-screen" data-admin-panel="true">
    @php
        $activePage = $activePage ?? '';
        $brandName = site_setting('brand.name', 'Manake');
        $logoUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $adminName = auth('admin')->user()->name ?? __('Admin');
        $adminRole = auth('admin')->user()->role ?? 'admin';
        $isSuperAdmin = auth('admin')->check() && $adminRole === 'super_admin';
        $locale = app()->getLocale();
        $currentTheme = $themePreference ?? request()->attributes->get('theme_preference', 'light');
        if (! in_array($currentTheme, ['system', 'dark', 'light'], true)) {
            $currentTheme = 'light';
        }
    @endphp

    <div x-data="{ sidebarOpen: false, adminSettingsOpen: false }" class="min-h-screen">
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

        <x-admin.sidebar
            :logo-url="$logoUrl"
            :brand-name="$brandName"
            :active-page="$activePage"
            :is-super-admin="$isSuperAdmin"
            :admin-name="$adminName"
            :admin-role="$adminRole"
        />

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-16 w-full max-w-[1320px] items-center justify-between gap-3 px-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Buka sidebar') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="7" x2="20" y2="7"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="17" x2="20" y2="17"></line>
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="truncate text-lg font-semibold text-blue-700 dark:text-blue-300">@yield('page_title', __('ui.admin.dashboard'))</h1>
                            <p class="text-xs text-blue-500/90 dark:text-blue-200/90">{{ __('ui.admin.panel_title') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="/" class="hidden text-sm font-semibold text-slate-600 transition hover:text-blue-600 sm:inline">{{ __('ui.admin.view_website') }}</a>
                        <div class="relative" @click.outside="adminSettingsOpen = false">
                            <button
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-600"
                                @click="adminSettingsOpen = !adminSettingsOpen"
                                :aria-expanded="adminSettingsOpen.toString()"
                                aria-label="{{ __('ui.nav.settings') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />
                                </svg>
                            </button>
                            <div x-cloak x-show="adminSettingsOpen" x-transition.origin.top.right class="card absolute right-0 mt-2 w-64 rounded-xl p-2 shadow-lg">
                                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('ui.nav.language') }}</p>
                                <div class="mb-2 grid grid-cols-2 gap-2 px-2">
                                    <a href="{{ route('lang.switch', ['locale' => 'id', 'redirect' => url()->full()]) }}" data-locale-option="id" class="rounded-lg border px-2 py-1.5 text-center text-xs font-semibold transition {{ $locale === 'id' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200 hover:text-blue-600' }}">
                                        {{ __('ui.languages.id') }}
                                    </a>
                                    <a href="{{ route('lang.switch', ['locale' => 'en', 'redirect' => url()->full()]) }}" data-locale-option="en" class="rounded-lg border px-2 py-1.5 text-center text-xs font-semibold transition {{ $locale === 'en' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200 hover:text-blue-600' }}">
                                        {{ __('ui.languages.en') }}
                                    </a>
                                </div>
                                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __('ui.nav.theme') }}</p>
                                <div class="mb-2 space-y-1 px-2">
                                    @foreach (['system' => __('ui.settings.theme_system'), 'dark' => __('ui.settings.theme_dark'), 'light' => __('ui.settings.theme_light')] as $value => $label)
                                        <a href="{{ route('theme.switch', ['theme' => $value, 'redirect' => url()->full()]) }}" data-theme-option="{{ $value }}" class="block rounded-lg border px-2 py-1.5 text-xs font-semibold transition {{ $currentTheme === $value ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-blue-200 hover:text-blue-600' }}">
                                            {{ $label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                            {{ strtoupper(substr($adminName, 0, 1)) }}
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                {{ __('ui.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 sm:py-8">
                <div class="mx-auto w-full max-w-[1320px]">
                    @yield('content')
                </div>
            </main>
        </div>
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
    </script>
</body>
</html>

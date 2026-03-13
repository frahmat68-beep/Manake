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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.overview.title') . ' | Manake')</title>
    @php
        $assetWithVersion = static function (string $file): string {
            return site_asset($file);
        };
        $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
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
    <div x-data="{ sidebarOpen: false, userOpen: false }" class="min-h-screen">
        <div class="fixed inset-0 z-40 bg-slate-900/40 transition lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 transform border-r border-slate-200 bg-white px-6 py-6 transition lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between">
                @php
                    $brandName = site_setting('brand.name', 'Manake');
                @endphp
                <a href="/" class="inline-flex items-center text-slate-900">
                    <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" :alt="$brandName" img-class="h-8 w-auto" />
                </a>
                <button class="lg:hidden text-slate-500" @click="sidebarOpen = false" aria-label="{{ __('ui.actions.close') }}">
                    ✕
                </button>
            </div>

            <nav class="mt-8 space-y-2 text-sm font-semibold">
                <a href="{{ route('overview') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('overview') ? 'btn-primary text-white' : 'btn-secondary text-slate-700' }}">
                    <span>{{ __('ui.nav.overview') }}</span>
                </a>
                <a href="{{ route('booking.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('booking.*') ? 'btn-primary text-white' : 'btn-secondary text-slate-700' }}">
                    <span>{{ __('ui.nav.booking') }}</span>
                </a>
                <a href="{{ route('cart') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('cart') ? 'btn-primary text-white' : 'btn-secondary text-slate-700' }}">
                    <span>{{ __('ui.nav.cart') }}</span>
                    @if (($cartCount ?? 0) > 0)
                        <span class="inline-flex min-w-[22px] items-center justify-center rounded-full bg-blue-600 px-2 py-0.5 text-[10px] text-white">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('settings.*') ? 'btn-primary text-white' : 'btn-secondary text-slate-700' }}">
                    <span>{{ __('ui.nav.settings') }}</span>
                </a>
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="mt-10">
                @csrf
                <button type="submit" class="btn-secondary w-full rounded-xl px-3 py-2 text-sm font-semibold transition">
                    {{ __('ui.nav.logout') }}
                </button>
            </form>
        </aside>

        <div class="lg:pl-72">
            <header class="manake-topbar-shell sticky top-0 z-30 border-b border-slate-200 bg-white">
                <div class="mx-auto flex w-full max-w-[1320px] flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <button data-ui-icon-button class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl" @click="sidebarOpen = true" aria-label="{{ __('ui.nav.toggle_menu') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                <line x1="4" y1="7" x2="20" y2="7" />
                                <line x1="4" y1="12" x2="20" y2="12" />
                                <line x1="4" y1="17" x2="20" y2="17" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-lg font-semibold text-slate-900">@yield('page_title', __('ui.overview.title'))</h1>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <div class="relative w-full sm:max-w-xs md:max-w-sm">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                            <input
                                type="text"
                                placeholder="{{ __('ui.nav.search_placeholder') }}"
                                class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            >
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="{{ route('settings.index') }}" data-ui-icon-button class="flex h-10 w-10 items-center justify-center rounded-xl transition" aria-label="{{ __('ui.nav.settings') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />
                                </svg>
                            </a>
                            <div class="relative" @click.outside="userOpen = false">
                                <button
                                    type="button"
                                    class="btn-secondary flex items-center gap-2 rounded-full px-2 py-1.5 transition"
                                    @click="userOpen = !userOpen"
                                    aria-haspopup="true"
                                    :aria-expanded="userOpen.toString()"
                                >
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                                        {{ strtoupper(substr(auth()->user()->display_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <span class="hidden text-sm font-semibold text-slate-700 sm:inline">
                                        {{ auth()->user()->display_name ?? auth()->user()->name ?? __('app.user.generic') }}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden sm:block h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div
                                    x-cloak
                                    x-show="userOpen"
                                    x-transition.origin.top.right
                                    class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white py-2 shadow-lg"
                                >
                                    <a href="{{ route('profile.complete') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('ui.nav.my_profile') }}</a>
                                    <div class="my-1 h-px bg-slate-100"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            {{ __('ui.nav.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-4 sm:px-6 sm:py-6">
                <div class="mx-auto w-full max-w-[1320px]">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

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

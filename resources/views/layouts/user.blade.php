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
    </style>
</head>
<body class="manake-shell" data-manake-shell="user">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <div class="fixed inset-0 z-40 bg-black/60 transition lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 transform border-r border-[#1A1A1E] bg-[#0A0A0B] px-6 py-6 transition lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            @php
                $brandName = site_setting('brand.name', 'Manake');
            @endphp
            <div class="flex items-center justify-between">
                <a href="/" class="inline-flex items-center text-[#E8E8EC]">
                    <x-brand.image light="manake-logo-blue.png" dark="manake-logo-blue.png" :alt="$brandName" img-class="h-8 w-auto" />
                </a>
                <button class="lg:hidden text-[#A0A0A8]" @click="sidebarOpen = false" aria-label="{{ __('ui.actions.close') }}">
                    ✕
                </button>
            </div>

            <nav class="mt-8 space-y-2 text-sm font-semibold">
                <a href="{{ route('overview') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('overview') ? 'btn-primary text-[#0A0A0B]' : 'btn-secondary text-[#E8E8EC]' }}">
                    <span>{{ __('ui.nav.overview') }}</span>
                    <span class="text-[10px] uppercase tracking-widest">{{ __('ui.overview.tag') }}</span>
                </a>
                <a href="{{ route('booking.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('booking.*') ? 'btn-primary text-[#0A0A0B]' : 'btn-secondary text-[#E8E8EC]' }}">
                    <span>{{ __('ui.nav.booking') }}</span>
                </a>
                <a href="{{ route('cart') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('cart') ? 'btn-primary text-[#0A0A0B]' : 'btn-secondary text-[#E8E8EC]' }}">
                    <span>{{ __('ui.nav.cart') }}</span>
                    @if (($cartCount ?? 0) > 0)
                        <span class="inline-flex min-w-[22px] items-center justify-center rounded-full bg-[#D4A843] px-2 py-0.5 text-[10px] text-[#0A0A0B]">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('settings.*') ? 'btn-primary text-[#0A0A0B]' : 'btn-secondary text-[#E8E8EC]' }}">
                    <span>{{ __('ui.nav.settings') }}</span>
                </a>
            </nav>

            <div class="mt-10 rounded-2xl border border-[#1A1A1E] bg-[#111113] p-4 text-xs text-[#A0A0A8]">
                <p class="font-semibold text-[#E8E8EC]">{{ __('ui.overview.quick_help_title') }}</p>
                <p class="mt-2">{{ __('ui.overview.quick_help_body') }}</p>
                <a href="/contact" class="btn-secondary mt-3 inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold transition">
                    {{ __('ui.actions.contact') }}
                </a>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="btn-secondary w-full rounded-xl px-3 py-2 text-sm font-semibold transition">
                    {{ __('ui.nav.logout') }}
                </button>
            </form>
        </aside>

        <div class="lg:pl-72">
            <header class="manake-topbar-shell sticky top-0 z-30 border-b border-[#1A1A1E] bg-[#0A0A0B]/90 backdrop-blur-xl">
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
                            <h1 class="text-lg font-semibold text-[#E8E8EC]">@yield('page_title', __('ui.overview.title'))</h1>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <div class="relative w-full sm:max-w-xs md:max-w-sm">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#66666C]">🔍</span>
                            <input
                                type="text"
                                placeholder="{{ __('ui.overview.search_placeholder') }}"
                                class="w-full rounded-xl border border-[#1A1A1E] bg-[#111113] pl-9 pr-3 py-2 text-sm text-[#E8E8EC] placeholder:text-[#71717A] focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/20 focus:outline-none"
                            >
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('catalog') }}" class="btn-secondary rounded-xl px-4 py-2 text-xs font-semibold transition">
                                {{ __('ui.actions.explore_catalog') }}
                            </a>
                            <div class="flex items-center gap-2 rounded-full border border-[#1A1A1E] bg-[#111113] px-2 py-1.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#D4A843] text-xs font-semibold text-[#0A0A0B]">
                                    {{ strtoupper(substr(auth()->user()->display_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="hidden text-sm font-semibold text-[#E8E8EC] sm:inline">
                                    {{ auth()->user()->display_name ?? auth()->user()->name ?? __('app.user.generic') }}
                                </span>
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

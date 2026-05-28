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
    <title>@yield('title', __('ui.admin.panel_title') . ' | Manake')</title>
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
        body[data-manake-shell="admin"] {
            --manake-heading-h1-resolved: #E8E8EC;
            --manake-heading-h2-resolved: #E8E8EC;
            --manake-heading-h3-resolved: #D4A843;
            --manake-heading-h4-resolved: #E8E8EC;
            --manake-body-color-resolved: #A0A0A8;
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
            background-color: #0A0A0B;
            color: #E8E8EC;
        }
        table tbody tr:focus-within td {
            background-color: #0A0A0B;
            color: #E8E8EC;
        }
        body[data-manake-shell="admin"] :is(.text-blue-300, .text-blue-400, .text-blue-500, .text-blue-600, .text-blue-700) {
            color: #D4A843 !important;
        }
        body[data-manake-shell="admin"] :is(.bg-blue-50, .bg-blue-100, .bg-blue-600, .bg-blue-700) {
            background-color: #D4A843 !important;
            color: #0A0A0B !important;
        }
        body[data-manake-shell="admin"] :is(.border-blue-200, .border-blue-300, .border-blue-400, .border-blue-500) {
            border-color: rgba(212, 168, 67, 0.42) !important;
        }
        body[data-manake-shell="admin"] :is(.hover\:text-blue-300:hover, .hover\:text-blue-600:hover, .hover\:text-blue-700:hover) {
            color: #D4A843 !important;
        }
        body[data-manake-shell="admin"] :is(.hover\:border-blue-200:hover, .hover\:border-blue-500\/40:hover) {
            border-color: rgba(212, 168, 67, 0.48) !important;
        }
        body[data-manake-shell="admin"] :is(.focus\:border-blue-400:focus) {
            border-color: #D4A843 !important;
        }
        body[data-manake-shell="admin"] :is(.focus\:ring-blue-500\/30:focus) {
            --tw-ring-color: rgba(212, 168, 67, 0.30) !important;
        }
    </style>
</head>
<body class="manake-shell" data-admin-panel="true" data-manake-shell="admin">
    @include('partials.page-loader')
    @php
        $activePage = $activePage ?? '';
        $brandName = site_setting('brand.name', 'Manake');
        $logoUrl = $assetWithVersion('manake-logo-white.png');
        $adminName = auth('admin')->user()->name ?? __('Admin');
        $adminRole = auth('admin')->user()->role ?? 'admin';
        $isSuperAdmin = auth('admin')->check() && $adminRole === 'super_admin';
        $locale = app()->getLocale();
        $currentTheme = $themePreference ?? request()->attributes->get('theme_preference', 'light');
        if (! in_array($currentTheme, ['system', 'dark', 'light'], true)) {
            $currentTheme = 'light';
        }
    @endphp

    <div x-data="{ sidebarOpen: false, sidebarCollapsed: false, adminSettingsOpen: false }" class="min-h-screen">
        <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false"></div>

        <x-admin.sidebar
            :logo-url="$logoUrl"
            :brand-name="$brandName"
            :active-page="$activePage"
            :is-super-admin="$isSuperAdmin"
            :admin-name="$adminName"
            :admin-role="$adminRole"
        />

        <div class="transition-all duration-300" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
            <header class="manake-topbar-shell sticky top-0 z-30 border-b border-[#1A1A1E] bg-[#0A0A0B]/95 text-[#E8E8EC] shadow-[0_14px_50px_rgba(0,0,0,0.18)] backdrop-blur-xl" data-manake-topbar="admin">
                <div class="mx-auto flex h-16 w-full max-w-[1320px] items-center justify-between gap-3 px-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" data-ui-icon-button class="inline-flex h-9 w-9 items-center justify-center rounded-xl lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Buka sidebar') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="7" x2="20" y2="7"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="17" x2="20" y2="17"></line>
                            </svg>
                        </button>
                        
                        <!-- Desktop Collapse Toggle -->
                        <button type="button" class="hidden h-9 w-9 items-center justify-center rounded-xl transition hover:bg-white/5 lg:flex" @click="sidebarCollapsed = !sidebarCollapsed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m15 18-6-6 6-6"/>
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="truncate text-lg font-semibold text-[#E8E8EC]">@yield('page_title', __('ui.admin.dashboard'))</h1>
                            <p class="text-xs text-[#A0A0A8]">{{ __('ui.admin.panel_title') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="/" data-ui-text-button class="hidden text-sm font-semibold transition sm:inline">{{ __('ui.admin.view_website') }}</a>
                        <div class="relative" @click.outside="adminSettingsOpen = false">
                            <button
                                type="button"
                                data-ui-icon-button
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl transition"
                                @click="adminSettingsOpen = !adminSettingsOpen"
                                :aria-expanded="adminSettingsOpen.toString()"
                                aria-label="{{ __('ui.nav.settings') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3 1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8 1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z" />
                                </svg>
                            </button>
                            <div x-cloak x-show="adminSettingsOpen" x-transition.origin.top.right class="absolute right-0 mt-2 w-[18.5rem]">
                                <x-preferences.popover :locale="$locale" :current-theme="$currentTheme" :redirect="url()->full()" />
                            </div>
                        </div>
                        <div class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#D4A843] text-sm font-semibold text-[#0A0A0B]">
                            {{ strtoupper(substr($adminName, 0, 1)) }}
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="btn-secondary rounded-xl px-3 py-1.5 text-xs font-semibold transition">
                                {{ __('ui.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="manake-main-stage px-4 py-4 sm:px-6 sm:py-6">
                <div class="mx-auto w-full max-w-[1320px]">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        window.fetchWithCsrf = async function (url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                ...(options.headers || {}),
            };

            return fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers,
            });
        };
        window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (window.axios && window.csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
        }
    </script>
    @include('partials.ui-feedback')
    @stack('scripts')
    @include('partials.theme-toggle')
</body>
</html>

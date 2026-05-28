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
    class="scroll-smooth {{ ($themeResolved ?? 'dark') === 'dark' ? 'dark' : '' }}"
    data-theme="{{ $themePreference ?? 'system' }}"
    data-theme-resolved="{{ $themeResolved ?? 'dark' }}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', site_setting('seo.meta_description', setting('meta_description', 'Manake Rental menyediakan rental alat produksi profesional: kamera, lighting, drone, dan audio.')))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', site_setting('seo.meta_title', setting('meta_title', setting('site_name', 'Manake'))))</title>
    <link rel="icon" type="image/png" href="{{ site_asset('MANAKE-FAV-M.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif;
        }
        body[data-manake-shell="app"] {
            overflow-x: hidden;
            background:
                radial-gradient(circle at var(--x, 50%) var(--y, 0%), rgba(212, 168, 67, 0.11), transparent 26rem),
                linear-gradient(180deg, #0A0A0B 0%, #111113 42%, #0A0A0B 100%);
            color: #A0A0A8;
        }
        body[data-manake-shell="app"] .manake-main-stage {
            min-height: 0;
        }
        body[data-manake-shell="app"] main :is(h1, h2, h4, h5, h6) {
            color: #E8E8EC;
            letter-spacing: 0;
        }
        body[data-manake-shell="app"] main h3 {
            color: #D4A843;
            letter-spacing: 0;
        }
        body[data-manake-shell="app"] main section {
            scroll-margin-top: 5rem;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] {
            background:
                radial-gradient(circle at var(--x, 50%) var(--y, 0%), rgba(212, 168, 67, 0.08), transparent 26rem),
                linear-gradient(180deg, #F8F8F5 0%, #F7F7F4 44%, #F3F1EB 100%);
            color: #666666;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] main :is(h1, h2, h4, h5, h6) {
            color: #171717;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] main h3 {
            color: #B8871F;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='bg-[#0A0A0B]'],
        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='bg-[#111113]'],
        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='bg-white/[0.03]'],
        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='bg-white/5'] {
            background-color: #FFFFFF !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='text-[#E8E8EC]'] {
            color: #171717 !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='text-[#A0A0A8]'] {
            color: #666666 !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='border-[#1A1A1E]'],
        html[data-theme-resolved='light'] body[data-manake-shell="app"] [class*='border-white/10'] {
            border-color: #E5E2DA !important;
        }
    </style>
</head>
<body class="manake-shell antialiased selection:bg-amber-500/10 selection:text-amber-500" data-manake-shell="app">
@include('partials.page-loader')

<div
    class="flex min-h-screen flex-col"
    x-data="{}"
    x-on:mousemove="
        const shell = $el.closest('[data-manake-shell]');
        if (shell) {
            shell.style.setProperty('--x', $event.clientX + 'px');
            shell.style.setProperty('--y', $event.clientY + 'px');
        }
    "
>
    @include('partials.navbar')

    <main class="manake-main-stage flex-1 px-4 py-6 sm:px-8 sm:py-8">
        <div class="mx-auto w-full max-w-[1320px]">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>

    @include('partials.footer')
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
<script>
    document.addEventListener('open-auth-modal', (event) => {
        const requestedView = typeof event.detail === 'string' ? event.detail : 'login';
        const target = requestedView === 'register' ? @json(route('register')) : @json(route('login'));
        window.location.assign(target);
    });
</script>
<x-chatbot-widget />
</body>
</html>

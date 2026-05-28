@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get('theme_resolved', $initialThemePreference === 'dark' ? 'dark' : 'light');
    $isHomepage = request()->routeIs('home');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth {{ ($themeResolved ?? 'dark') === 'dark' ? 'dark' : '' }}" data-theme="{{ $themePreference ?? 'system' }}" data-theme-resolved="{{ $themeResolved ?? 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', site_setting('seo.meta_description', setting('meta_description', 'Manake Rental menyediakan rental alat produksi profesional: kamera, lighting, drone, dan audio.')))">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', site_setting('seo.meta_title', setting('meta_title', setting('site_name', 'Manake'))))</title>
    <link rel="icon" type="image/png" href="{{ site_asset('MANAKE-FAV-M.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    @include('partials.runtime-ui-assets')
    @stack('head')
    <style>
        [x-cloak] { display: none !important; }
        body[data-manake-shell="landing"] {
            overflow-x: hidden;
            background:
                radial-gradient(circle at var(--x, 50%) var(--y, 0%), rgba(212, 168, 67, 0.10), transparent 28rem),
                #0A0A0B;
            color: #A0A0A8;
        }
        body[data-manake-shell="landing"] section {
            scroll-margin-top: 5rem;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="landing"] {
            background:
                radial-gradient(circle at var(--x, 50%) var(--y, 0%), rgba(212, 168, 67, 0.07), transparent 28rem),
                #F7F7F4;
            color: #666666;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='bg-[#0A0A0B]'],
        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='bg-[#111113]'],
        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='bg-white/[0.03]'],
        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='bg-white/5'] {
            background-color: #FFFFFF !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='text-[#E8E8EC]'] {
            color: #171717 !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='text-[#A0A0A8]'] {
            color: #666666 !important;
        }

        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='border-[#1A1A1E]'],
        html[data-theme-resolved='light'] body[data-manake-shell="landing"] [class*='border-white/10'] {
            border-color: #E5E2DA !important;
        }
    </style>
</head>
<body class="ui-shell landing-shell antialiased selection:bg-amber-500/20 selection:text-amber-200" data-manake-shell="landing">
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

    <main class="landing-main flex-1">
        @yield('content')
    </main>

    @unless($isHomepage)
        @include('partials.footer')
    @endunless
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

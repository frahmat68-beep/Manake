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
            min-height: calc(100vh - 4rem);
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
    </style>
</head>
<body class="manake-shell antialiased selection:bg-amber-500/10 selection:text-amber-500" data-manake-shell="app">
@include('partials.page-loader')

<div
    class="min-h-screen"
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

    <main class="manake-main-stage px-4 py-6 sm:px-8 sm:py-8">
        <div class="mx-auto w-full max-w-[1320px]">
            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </main>

    @include('partials.footer')
</div>

@include('partials.ui-feedback')
@stack('scripts')
@include('partials.theme-toggle')
<script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (window.axios && window.csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
    }
    window.fetchWithCsrf = (url, options = {}) => {
        const headers = new Headers(options.headers || {});
        if (window.csrfToken) {
            headers.set('X-CSRF-TOKEN', window.csrfToken);
        }
        return fetch(url, { ...options, headers });
    };
    document.addEventListener('open-auth-modal', (event) => {
        const requestedView = typeof event.detail === 'string' ? event.detail : 'login';
        const target = requestedView === 'register' ? @json(route('register')) : @json(route('login'));
        window.location.assign(target);
    });
</script>
<x-chatbot-widget />
</body>
</html>

@php
    $initialThemePreference = $themePreference ?? request()->attributes->get('theme_preference', 'light');
    $initialThemeResolved = $themeResolved ?? request()->attributes->get(
        'theme_resolved',
        $initialThemePreference === 'dark' ? 'dark' : 'light'
    );
@endphp
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="scroll-smooth {{ $initialThemeResolved === 'dark' ? 'dark' : '' }}"
    data-theme="manake-brand"
    data-theme-preference="{{ $initialThemePreference }}"
    data-theme-resolved="{{ $initialThemeResolved }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $assetWithVersion = static function (string $file): string {
                return site_asset($file);
            };

            $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
            $brandName = site_setting('brand.name', config('app.name', 'Manake'));
            $pageTitle = $pageTitle ?: $brandName;
            $backUrl = $backUrl ?: route('home');
            $backLabel = $backLabel ?: __('app.auth.back_home');
            $asidePoints = array_values(array_filter($asidePoints));
        @endphp

        <title>{{ $pageTitle }}</title>
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
        @include('partials.theme-init')
        @include('partials.runtime-ui-assets')
        <style>
            [x-cloak] { display: none !important; }
            body { font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, sans-serif; }

            /* ===== AUTH THEME VARIABLES ===== */
            .auth-page {
                --auth-accent: #D4A843;
                --auth-accent-hover: #E0BA5D;
                --auth-accent-text: #0A0A0B;
                --auth-accent-soft: rgba(212, 168, 67, 0.12);
                --auth-accent-border: rgba(212, 168, 67, 0.28);
                --auth-accent-glow: rgba(212, 168, 67, 0.10);
                --auth-page-bg: #0A0A0B;
                --auth-panel-bg: #0A0A0B;
                --auth-panel-text: #E8E8EC;
                --auth-text: #E8E8EC;
                --auth-muted: #A0A0A8;
                --auth-input-bg: rgba(10, 10, 11, 0.50);
                --auth-input-border: #1A1A1E;
                --auth-input-text: #E8E8EC;
                --auth-input-placeholder: #66666C;
                --auth-google-bg: transparent;
                --auth-google-border: #1A1A1E;
            }

            html[data-theme-resolved="light"] .auth-page {
                --auth-accent: #2563EB;
                --auth-accent-hover: #1D4ED8;
                --auth-accent-text: #FFFFFF;
                --auth-accent-soft: rgba(37, 99, 235, 0.10);
                --auth-accent-border: rgba(37, 99, 235, 0.24);
                --auth-accent-glow: rgba(37, 99, 235, 0.10);
                --auth-page-bg: #F8FAFC;
                --auth-panel-bg: #FFFFFF;
                --auth-panel-text: #111827;
                --auth-text: #111827;
                --auth-muted: #4B5563;
                --auth-input-bg: #FFFFFF;
                --auth-input-border: #DADDE3;
                --auth-input-text: #111827;
                --auth-input-placeholder: #6B7280;
                --auth-google-bg: #FFFFFF;
                --auth-google-border: #E5E7EB;
            }

            /* ===== AUTH LAYOUT ===== */
            .auth-page-bg {
                background-color: var(--auth-page-bg) !important;
                color: var(--auth-text) !important;
            }

            /* Left showcase — stable 50/50 split, full viewport height */
            .auth-showcase {
                position: relative;
                overflow: hidden;
                min-height: 100vh;
            }

            /* Right form panel — same stable height */
            .auth-form-panel {
                background-color: var(--auth-panel-bg) !important;
                color: var(--auth-panel-text) !important;
                min-height: 100vh;
            }

            /* Light mode panel shadow */
            html[data-theme-resolved="light"] .auth-form-panel {
                box-shadow: -20px 0 60px -20px rgba(15, 23, 42, 0.10);
            }

            /* ===== AUTH UTILITY CLASSES ===== */
            .auth-title {
                color: var(--auth-text) !important;
            }

            .auth-muted {
                color: var(--auth-muted) !important;
            }

            .auth-accent-text {
                color: var(--auth-accent) !important;
            }

            /* CRITICAL: use `background` shorthand to defeat any gradient from btn-primary */
            .auth-accent-bg {
                background: var(--auth-accent) !important;
                background-color: var(--auth-accent) !important;
                color: var(--auth-accent-text) !important;
                border: 1px solid var(--auth-accent) !important;
            }

            .auth-accent-bg:hover {
                background: var(--auth-accent-hover) !important;
                background-color: var(--auth-accent-hover) !important;
                border-color: var(--auth-accent-hover) !important;
            }

            .auth-accent-bg:focus-visible {
                outline: none !important;
                box-shadow: 0 0 0 3px var(--auth-accent-soft) !important;
            }

            .auth-link {
                color: var(--auth-accent) !important;
            }

            .auth-link:hover {
                color: var(--auth-accent-hover) !important;
                text-decoration: underline;
            }

            .auth-input {
                border: 1px solid var(--auth-input-border) !important;
                background-color: var(--auth-input-bg) !important;
                color: var(--auth-input-text) !important;
                outline: none !important;
            }

            .auth-input::placeholder {
                color: var(--auth-input-placeholder) !important;
            }

            .auth-input:focus {
                border-color: var(--auth-accent) !important;
                box-shadow: 0 0 0 2px var(--auth-accent-soft) !important;
            }

            /* Autofill suppression — prevents blue tint on light mode */
            .auth-page input.auth-input:-webkit-autofill,
            .auth-page input.auth-input:-webkit-autofill:hover,
            .auth-page input.auth-input:-webkit-autofill:focus {
                -webkit-text-fill-color: var(--auth-input-text) !important;
                box-shadow: 0 0 0 1000px var(--auth-input-bg) inset !important;
                caret-color: var(--auth-input-text) !important;
                transition: background-color 9999s ease-in-out 0s !important;
            }

            html[data-theme-resolved="light"] .auth-page input.auth-input:-webkit-autofill,
            html[data-theme-resolved="light"] .auth-page input.auth-input:-webkit-autofill:hover,
            html[data-theme-resolved="light"] .auth-page input.auth-input:-webkit-autofill:focus {
                -webkit-text-fill-color: #111827 !important;
                box-shadow: 0 0 0 1000px #FFFFFF inset !important;
            }

            .auth-divider-line {
                border-color: var(--auth-input-border) !important;
            }

            .auth-divider-label {
                background-color: var(--auth-panel-bg) !important;
                color: var(--auth-muted) !important;
            }

            .auth-google-button {
                background-color: var(--auth-google-bg) !important;
                border-color: var(--auth-google-border) !important;
                color: var(--auth-text) !important;
            }

            .auth-google-button:hover {
                border-color: var(--auth-accent-border) !important;
                color: var(--auth-accent) !important;
            }

            .auth-mobile-glow {
                background:
                    radial-gradient(circle at top, var(--auth-accent-glow), transparent 28%),
                    radial-gradient(circle at bottom, var(--auth-accent-soft), transparent 22%) !important;
            }

            .auth-back-home:hover {
                color: var(--auth-accent) !important;
            }

            .auth-aside-check-icon {
                color: var(--auth-accent) !important;
            }

            /* ===== AUTH FORM CARD ===== */
            .auth-form-card {
                width: 100%;
                max-width: 28rem;
                min-height: 0;
            }

            /* Form heading — same size across all auth pages */
            .auth-form-heading {
                color: var(--auth-text) !important;
                font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
                font-size: clamp(1.75rem, 3vw, 2rem);
                font-weight: 800;
                line-height: 1.15;
                letter-spacing: -0.02em;
                margin-bottom: 2rem;
            }

            /* Auth logo — consistent sizing, color handled by asset swap */
            .auth-logo {
                display: block !important;
                height: 2.5rem !important;
                width: auto !important;
                max-width: 11rem !important;
                object-fit: contain !important;
                filter: drop-shadow(0 4px 18px rgba(0, 0, 0, 0.45));
            }

            /* ===== LEFT SHOWCASE IMAGE + OVERLAYS ===== */
            .auth-showcase-image {
                filter: saturate(0.96) contrast(1.04) brightness(0.78);
            }

            html[data-theme-resolved="dark"] .auth-showcase-image {
                filter: saturate(0.96) contrast(1.04) brightness(0.72);
            }

            /* Main cinematic overlay — same structure in light/dark */
            .auth-showcase-overlay {
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(180deg, rgba(10, 10, 11, 0.18) 0%, rgba(10, 10, 11, 0.30) 45%, rgba(10, 10, 11, 0.62) 100%),
                    linear-gradient(90deg, rgba(10, 10, 11, 0.22) 0%, rgba(10, 10, 11, 0.08) 52%, rgba(10, 10, 11, 0.20) 100%);
            }

            /* Accent tint overlay */
            .auth-left-accent-overlay {
                background-color: var(--auth-accent-glow) !important;
            }

            /* Scrim behind text — strong bottom-up gradient for readability */
            .auth-showcase-text-scrim {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 60%;
                background: linear-gradient(0deg, rgba(10, 10, 11, 0.90) 0%, rgba(10, 10, 11, 0.64) 46%, rgba(10, 10, 11, 0.10) 100%);
            }

            /* ===== LEFT SHOWCASE TEXT — always white regardless of theme ===== */
            .auth-showcase-copy {
                position: absolute;
                bottom: 4rem;
                left: 4rem;
                right: 4rem;
                z-index: 10;
                max-width: 36rem;
            }

            .auth-showcase-title {
                color: #FFFFFF !important;
                font-family: Georgia, "Times New Roman", serif;
                font-size: clamp(2.65rem, 4vw, 3.45rem);
                font-weight: 700;
                line-height: 0.98;
                letter-spacing: -0.02em;
                margin-bottom: 1.5rem;
                text-shadow: 0 4px 24px rgba(0, 0, 0, 0.88);
            }

            .auth-showcase-subtitle {
                color: #E5E7EB !important;
                font-size: 1.0625rem;
                line-height: 1.65;
                max-width: 34rem;
                text-shadow: 0 3px 18px rgba(0, 0, 0, 0.78);
            }

            .auth-showcase-list-item {
                color: #FFFFFF !important;
                text-shadow: 0 2px 12px rgba(0, 0, 0, 0.65);
            }

            .auth-slot form {
                width: 100%;
            }

            .auth-forgot-intro {
                margin-bottom: 1rem;
            }
        </style>
        {{ $head ?? '' }}
    </head>
    <body class="manake-shell antialiased" data-manake-shell="auth">
        @include('partials.page-loader')

        @php
            $compactAuthShowcaseTitle = $asideHeading ?: null;
            $compactAuthShowcaseText = $asideText ?: null;
        @endphp

        <div class="auth-page auth-page-bg flex min-h-screen w-full overflow-hidden">
            <!-- Left Side: Image / Showcase (hidden on mobile) -->
            <div class="auth-showcase relative hidden min-h-screen w-1/2 overflow-hidden lg:block">
                <a href="{{ route('home') }}" class="absolute top-10 left-12 z-20 transition-transform hover:scale-105 w-fit" data-skip-loader="true">
                    <x-brand.image
                        light="manake-logo-white.png"
                        dark="manake-logo-white.png"
                        alt="{{ $brandName }}"
                        img-class="auth-logo h-10 w-auto object-contain"
                        :swap-in-dark="false"
                    />
                </a>

                <!-- Background image -->
                <img
                    src="{{ site_asset('images/hero-bg-light.jpg') }}"
                    alt="Cinematic Camera"
                    class="auth-showcase-image absolute inset-0 h-full w-full object-cover"
                >

                <!-- Cinematic overlay -->
                <div class="auth-showcase-overlay absolute inset-0"></div>

                <!-- Accent tint -->
                <div class="auth-left-accent-overlay absolute inset-0 mix-blend-overlay opacity-40"></div>

                <!-- Bottom scrim for text readability -->
                <div class="auth-showcase-text-scrim absolute bottom-0 left-0 right-0 h-[60%]"></div>

                <!-- Showcase Copy -->
                <div class="auth-showcase-copy absolute bottom-16 left-16 right-16 z-10 max-w-[36rem]">
                    @if ($compactAuthShowcaseTitle)
                        <h1 class="auth-showcase-title mb-6 font-serif text-[clamp(2.65rem,4vw,3.45rem)] font-bold leading-[0.98] tracking-tight">
                            {{ $compactAuthShowcaseTitle }}
                        </h1>
                    @endif

                    @if ($compactAuthShowcaseText)
                        <p class="auth-showcase-subtitle max-w-xl text-base leading-relaxed sm:text-lg">
                            {{ $compactAuthShowcaseText }}
                        </p>
                    @else
                        <p class="auth-showcase-subtitle max-w-xl text-base leading-relaxed sm:text-lg">
                            {{ __('ui.auth.aside_default_text') }}
                        </p>
                    @endif

                    @if(count($asidePoints) > 0)
                        <ul class="mt-8 space-y-3">
                            @foreach($asidePoints as $point)
                            <li class="auth-showcase-list-item flex items-center gap-3 font-medium">
                                <svg class="auth-aside-check-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $point }}
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="auth-form-panel relative flex min-h-screen w-full flex-col justify-center px-6 py-12 sm:px-16 md:px-24 lg:w-1/2">
                <!-- Mobile glow (no left image on small screens) -->
                <div class="auth-mobile-glow pointer-events-none absolute inset-0 lg:hidden"></div>

                <div class="auth-form-card relative z-10 mx-auto w-full max-w-md">
                    <!-- Mobile logo (only shows when left panel is hidden) -->
                    <a href="{{ route('home') }}" class="mb-12 flex items-center transition-transform hover:scale-105 w-fit lg:hidden" data-skip-loader="true">
                        <x-brand.image
                            light="manake-logo-white.png"
                            dark="manake-logo-white.png"
                            alt="{{ $brandName }}"
                            img-class="auth-logo h-10 w-auto object-contain"
                            :swap-in-dark="false"
                        />
                    </a>

                    @if ($heading ?? null)
                        <h2 class="auth-form-heading">
                            {{ $heading }}
                        </h2>
                    @endif

                    <div class="auth-slot flex w-full flex-col gap-5">
                        {{ $slot }}
                    </div>

                    @if ($showBackHome ?? true)
                        <a href="{{ $backUrl }}" class="auth-back-home auth-muted mt-10 inline-flex w-fit items-center text-sm font-semibold transition" data-skip-loader="true">
                            &larr; {{ $backLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <x-chatbot-widget />
        @include('partials.theme-toggle')
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
    </body>
</html>

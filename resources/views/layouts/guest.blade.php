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

            .auth-page-bg {
                background-color: var(--auth-page-bg) !important;
                color: var(--auth-text) !important;
            }

            .auth-form-panel {
                background-color: var(--auth-panel-bg) !important;
                color: var(--auth-panel-text) !important;
            }

            .auth-title {
                color: var(--auth-text) !important;
            }

            .auth-muted {
                color: var(--auth-muted) !important;
            }

            .auth-accent-text {
                color: var(--auth-accent) !important;
            }

            .auth-accent-bg {
                background-color: var(--auth-accent) !important;
                color: var(--auth-accent-text) !important;
                border-color: var(--auth-accent) !important;
            }

            .auth-accent-bg:hover {
                background-color: var(--auth-accent-hover) !important;
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

            .auth-left-accent-overlay {
                background-color: var(--auth-accent-glow) !important;
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

            /* Light mode shadow on form panel */
            html[data-theme-resolved="light"] .auth-form-panel {
                box-shadow: -20px 0 60px -20px rgba(15, 23, 42, 0.08);
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

        <div class="auth-page auth-page-bg flex min-h-screen w-full">
            <!-- Left Side: Image / Showcase (hidden on mobile) -->
            <div class="relative hidden w-1/2 lg:block">
                <a href="{{ route('home') }}" class="absolute top-10 left-12 z-20 transition-transform hover:scale-105 w-fit" data-skip-loader="true">
                    <x-brand.image
                        light="manake-logo-blue.png"
                        dark="manake-logo-white.png"
                        alt="{{ $brandName }}"
                        img-class="h-10 w-auto object-contain"
                        :swap-in-dark="true"
                    />
                </a>
                <img src="{{ site_asset('images/hero-bg-light.jpg') }}" alt="Cinematic Camera" class="absolute inset-0 h-full w-full object-cover">
                <!-- Gradient Overlay: balanced so text is readable without being too dark -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#0A0A0B]/85 via-[#0A0A0B]/30 to-[#0A0A0B]/10"></div>
                <div class="auth-left-accent-overlay absolute inset-0 mix-blend-overlay opacity-60"></div>
                <!-- Extra readability gradient behind showcase text -->
                <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-[#0A0A0B]/70 via-[#0A0A0B]/20 to-transparent"></div>
                
                <!-- Showcase Content -->
                <div class="absolute bottom-16 left-16 right-16 z-10">
                    @if ($compactAuthShowcaseTitle)
                        <h1 class="font-serif text-5xl font-bold tracking-tight text-white mb-6 drop-shadow-[0_4px_20px_rgba(0,0,0,0.65)]">
                            {{ $compactAuthShowcaseTitle }}
                        </h1>
                    @endif
                    @if ($compactAuthShowcaseText)
                        <p class="max-w-xl text-lg text-[#D6D6DC] drop-shadow-[0_3px_16px_rgba(0,0,0,0.65)]">
                            {{ $compactAuthShowcaseText }}
                        </p>
                    @else
                        <p class="max-w-xl text-lg text-[#D6D6DC] drop-shadow-[0_3px_16px_rgba(0,0,0,0.65)]">
                            {{ __('ui.auth.aside_default_text') }}
                        </p>
                    @endif
                    
                    @if(count($asidePoints) > 0)
                        <ul class="mt-8 space-y-3">
                            @foreach($asidePoints as $point)
                            <li class="flex items-center gap-3 text-white font-medium">
                                <svg class="auth-aside-check-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <div class="auth-form-panel relative flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 sm:px-16 md:px-24">
                <!-- Subtle background glow for mobile where there's no left side image -->
                <div class="auth-mobile-glow pointer-events-none absolute inset-0 lg:hidden"></div>

                <div class="mx-auto w-full max-w-md relative z-10">
                    <a href="{{ route('home') }}" class="mb-12 flex items-center transition-transform hover:scale-105 w-fit lg:hidden" data-skip-loader="true">
                        <x-brand.image
                            light="manake-logo-blue.png"
                            dark="manake-logo-white.png"
                            alt="{{ $brandName }}"
                            img-class="h-10 w-auto object-contain"
                            :swap-in-dark="true"
                        />
                    </a>

                    @if ($heading ?? null)
                        <h2 class="auth-title mb-8 font-serif text-3xl font-bold tracking-tight">
                            {{ $heading }}
                        </h2>
                    @endif

                    <div class="flex w-full flex-col gap-5">
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

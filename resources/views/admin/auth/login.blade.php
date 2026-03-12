<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('ui.admin.admin_login') }} | Manake.Id</title>
    @php
        $assetWithVersion = static function (string $file): string {
            return site_asset($file);
        };
        $faviconLightUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $faviconDarkUrl = $assetWithVersion('MANAKE-FAV-M-white.png');
        $logoFallbackLight = $assetWithVersion('manake-logo-blue.png');
        $cmsBrandLogoPath = site_setting('brand.logo_path');
        $logoUrlLight = site_media_url($cmsBrandLogoPath) ?: $logoFallbackLight;
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
    @vite(['resources/css/theme.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen" data-manake-shell="auth">
    @include('partials.page-loader')
    <div class="manake-auth-shell min-h-screen px-4 py-8 sm:px-6 sm:py-10">
        <div class="manake-auth-card mx-auto w-full max-w-5xl overflow-hidden rounded-[2rem] lg:grid lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
            <div class="manake-auth-panel p-6 text-slate-800 sm:p-8 lg:p-10">
                <div class="space-y-5">
                    <a href="/" class="manake-brand-pill inline-flex items-center rounded-2xl px-4 py-3 shadow-sm" data-skip-loader="true">
                        <img src="{{ $logoUrlLight }}" alt="Manake" class="h-10 w-auto" onerror="this.onerror=null;this.src='{{ $logoFallbackLight }}';">
                    </a>
                    <div class="space-y-2">
                        <span class="manake-kicker">{{ __('ui.admin.panel_title') }}</span>
                        <h2 class="text-3xl font-semibold tracking-[-0.03em] text-slate-950">{{ __('ui.admin.admin_login') }}</h2>
                        <p class="max-w-md text-sm leading-6 text-slate-500">{{ __('ui.admin.login_intro') }}</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('ui.admin.email') }}</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="input mt-2 w-full rounded-2xl px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            placeholder="admin@manake.id"
                        >
                    </div>

                    <div class="relative">
                        <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('ui.admin.password') }}</label>
                        <x-password-input
                            id="admin-auth-password"
                            name="password"
                            :required="true"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-2xl px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <button class="btn-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold transition">
                        {{ __('ui.admin.login_button') }}
                    </button>
                </form>
            </div>
            <div class="manake-auth-showcase hidden p-8 text-slate-100 lg:block lg:p-10">
                <span class="manake-kicker manake-kicker-inverse">Private workspace</span>
                <h1 class="mt-6 text-3xl font-semibold leading-tight tracking-[-0.04em] md:text-4xl">
                    {{ __('ui.admin.login_heading') }}
                </h1>
                <p class="mt-4 max-w-md text-sm leading-7 text-blue-100/82">
                    {{ __('ui.admin.login_subheading') }}
                </p>

                <div class="manake-auth-matrix mt-8">
                    <article class="manake-auth-chip">
                        <span>Catalog</span>
                        <strong>Live sync</strong>
                    </article>
                    <article class="manake-auth-chip">
                        <span>Orders</span>
                        <strong>Operational board</strong>
                    </article>
                    <article class="manake-auth-chip">
                        <span>Content</span>
                        <strong>One panel control</strong>
                    </article>
                </div>

                <div class="manake-auth-note mt-8 rounded-[1.6rem] border border-white/15 bg-white/10 p-5 text-sm text-slate-200">
                    {{ __('ui.admin.login_hint') }}
                </div>
                <a href="/" class="mt-6 inline-flex items-center justify-center rounded-2xl border border-white/20 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10" data-skip-loader="true">
                    {{ __('ui.admin.back_home') }}
                </a>
            </div>
        </div>
    </div>
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

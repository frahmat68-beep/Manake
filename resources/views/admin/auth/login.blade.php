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
        $logoUrlLight = $logoFallbackLight;
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
<body class="min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
        <div class="card w-full max-w-4xl overflow-hidden rounded-3xl shadow-2xl lg:grid lg:grid-cols-2">
            <div class="p-6 text-slate-800 sm:p-8 lg:p-10">
                <div>
                    <a href="/" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        <img src="{{ $logoUrlLight }}" alt="Manake" class="h-10 w-auto" onerror="this.onerror=null;this.src='{{ $logoFallbackLight }}';">
                    </a>
                    <div>
                        <h2 class="text-2xl font-semibold text-blue-700">{{ __('ui.admin.admin_login') }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ __('ui.admin.login_intro') }}</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('ui.admin.email') }}</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            placeholder="admin@manake.id"
                        >
                    </div>

                    <div class="relative">
                        <label class="text-xs font-semibold text-slate-500">{{ __('ui.admin.password') }}</label>
                        <x-password-input
                            id="admin-auth-password"
                            name="password"
                            :required="true"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        {{ __('ui.admin.login_button') }}
                    </button>
                </form>
            </div>
            <div class="hidden p-8 text-slate-100 lg:block lg:p-10 bg-gradient-to-br from-slate-950 via-blue-900 to-slate-900">
                <img src="{{ $logoUrlLight }}" alt="Manake" class="h-12 w-auto rounded-xl bg-white p-2" onerror="this.onerror=null;this.src='{{ $logoFallbackLight }}';">
                <h1 class="mt-6 text-2xl md:text-3xl font-semibold leading-tight">
                    {{ __('ui.admin.login_heading') }}
                </h1>
                <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                    {{ __('ui.admin.login_subheading') }}
                </p>
                <div class="mt-6 rounded-2xl border border-white/20 bg-white/10 p-4 text-sm text-slate-200">
                    {{ __('ui.admin.login_hint') }}
                </div>
                <a href="/" class="mt-6 inline-flex items-center justify-center rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
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

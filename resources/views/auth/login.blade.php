<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.auth.login_page_title') }}</title>
    @php
        $assetWithVersion = static function (string $file): string {
            return site_asset($file);
        };
        $faviconUrl = $assetWithVersion('MANAKE-FAV-M.png');
        $defaultLogoUrl = $assetWithVersion('manake-logo-blue.png');
        $cmsBrandLogoPath = site_setting('brand.logo_path');
        $logoUrl = site_media_url($cmsBrandLogoPath) ?: $defaultLogoUrl;
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/theme.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
        <div class="card w-full max-w-5xl overflow-hidden rounded-3xl shadow-xl lg:grid lg:grid-cols-2">
            <div class="p-6 sm:p-8 lg:p-10">
                <a href="/" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                    <img src="{{ $logoUrl }}" alt="Manake" class="h-10 w-auto">
                </a>
                <h2 class="text-2xl font-semibold text-blue-700">{{ __('app.auth.login_title') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('app.auth.login_subheading') }}</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.email') }}</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            placeholder="{{ __('app.auth.email_placeholder') }}"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('app.auth.password') }}</label>
                        <x-password-input
                            id="login-password"
                            name="password"
                            :required="true"
                            placeholder="{{ __('app.auth.password_placeholder_mask') }}"
                            autocomplete="current-password"
                            wrapper-class="mt-2"
                            input-class="input w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        />
                    </div>

                    <button class="btn-primary w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                        {{ __('app.auth.login_button') }}
                    </button>
                </form>

                <div class="mt-4 flex flex-col gap-2 text-sm text-slate-500">
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-700">{{ __('app.auth.forgot_password') }}</a>
                    <p>
                        {{ __('app.auth.no_account') }}
                        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">{{ __('app.auth.register_now') }}</a>
                    </p>
                </div>
            </div>

            <div class="relative hidden p-8 text-white lg:block lg:p-10 bg-gradient-to-br from-slate-950 via-blue-900 to-slate-900">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top,_white,_transparent_60%)]"></div>
                <div class="relative z-10">
                    <img src="{{ $logoUrl }}" alt="Manake" class="h-12 w-auto bg-white rounded-xl p-2">
                    <h1 class="mt-6 text-2xl md:text-3xl font-semibold leading-tight">
                        {{ __('app.auth.login_heading') }}
                    </h1>
                    <p class="mt-3 text-sm text-blue-100 leading-relaxed">
                        {{ __('app.auth.login_note') }}
                    </p>
                    <div class="mt-6 space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>{{ __('app.auth.login_benefit_1') }}</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>{{ __('app.auth.login_benefit_2') }}</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-white"></span>
                            <p>{{ __('app.auth.login_benefit_3') }}</p>
                        </div>
                    </div>
                    <a href="/" class="mt-6 inline-flex items-center justify-center rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                        {{ __('app.auth.back_home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('ui.auth.forgot_title') }} | Manake.Id</title>
    <link rel="icon" type="image/png" href="{{ site_asset('MANAKE-FAV-M.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/theme.css'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: "Plus Jakarta Sans", system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl grid md:grid-cols-2">
            <div class="relative p-8 md:p-10 bg-gradient-to-br from-slate-900 via-blue-900 to-blue-700 text-white">
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_white,_transparent_60%)]"></div>
                <div class="relative z-10">
                    <img src="{{ site_asset('manake-logo-blue.png') }}" alt="Manake" class="h-12 w-auto bg-white rounded-xl p-2">
                    <h1 class="mt-6 text-2xl md:text-3xl font-semibold leading-tight">
                        {{ __('ui.auth.forgot_heading') }}
                    </h1>
                    <p class="mt-3 text-sm text-blue-100 leading-relaxed">
                        {{ __('ui.auth.forgot_subheading') }}
                    </p>
                    <div class="mt-6 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-xs text-blue-100">
                        {{ __('ui.auth.forgot_hint') }}
                    </div>
                    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center justify-center rounded-xl border border-white/30 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                        {{ __('ui.auth.back_to_login') }}
                    </a>
                </div>
            </div>

            <div class="p-8 md:p-10">
                <h2 class="text-2xl font-semibold text-blue-700">{{ __('ui.auth.forgot_title') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __('ui.auth.forgot_help') }}</p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('ui.auth.email_label') }}</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            placeholder="{{ __('ui.auth.email_placeholder') }}"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        {{ __('ui.auth.forgot_button') }}
                    </button>
                </form>

                <p class="mt-4 text-xs text-slate-500">{{ __('ui.auth.forgot_note') }}</p>
            </div>
        </div>
    </div>

    @include('partials.theme-toggle')
</body>
</html>

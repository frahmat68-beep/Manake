<x-guest-layout
    :page-title="__('ui.auth.login_page_title')"
    :eyebrow="null"
    :heading="__('ui.auth.login_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="__('ui.auth.login_aside_heading')"
    :aside-text="__('ui.auth.login_aside_text')"
    :aside-points="[]"
>
    <!-- Global Errors/Status -->
    @if (session('error') || session('status') || $errors->any())
        <div class="mb-2 flex w-full flex-col gap-2">
            @if (session('error'))
            <div class="text-center text-sm text-rose-300">{{ session('error') }}</div>
            @endif
            @if (session('status'))
            <div class="text-center text-sm text-emerald-300">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
            <div class="text-center text-sm text-rose-300">{{ $errors->first() }}</div>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-full flex flex-col gap-4">
        @csrf

        <div class="space-y-1">
            <label for="email" class="auth-title block text-sm font-medium">{{ __('ui.auth.email_label') }}</label>
            <input
                id="email"
                placeholder="{{ __('ui.auth.email_placeholder') }}"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="auth-input input w-full rounded-xl px-4 py-3 text-sm transition-all"
            />
        </div>
        
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <label for="password" class="auth-title block text-sm font-medium">{{ __('ui.auth.password_label') }}</label>
                <a href="{{ route('password.request') }}" class="auth-link text-xs font-medium transition-colors" data-skip-loader="true">
                    {{ __('ui.auth.forgot_password') }}
                </a>
            </div>
            <x-password-input
                id="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="{{ __('ui.auth.password_placeholder') }}"
                input-class="auth-input input w-full rounded-xl px-4 py-3 text-sm transition-all"
            >
            </x-password-input>
        </div>
        
        <button type="submit" class="auth-accent-bg btn-primary mt-2 w-full rounded-xl px-5 py-3.5 text-sm font-bold transition-all active:scale-95 shadow-[0_16px_30px_-18px_rgba(0,0,0,0.35)]">
            {{ __('ui.auth.login_button') }}
        </button>
        
        <div class="relative mt-2">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="auth-divider-line w-full border-t"></div>
            </div>
            <div class="relative flex justify-center text-sm font-medium leading-6">
                <span class="auth-divider-label px-6">{{ __('ui.auth.divider_or') }}</span>
            </div>
        </div>

        <x-auth.google-button
            :label="__('ui.auth.continue_google')"
            class="w-full flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-sm font-semibold transition-all border"
        />
        
        <div class="mt-4 w-full text-center">
            <span class="auth-muted text-sm">
                {{ __('ui.auth.no_account') }} 
                <a href="{{ route('register') }}" class="auth-link font-semibold transition" data-skip-loader="true">
                    {{ __('ui.auth.register_now') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

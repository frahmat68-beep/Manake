<x-guest-layout
    :page-title="__('ui.auth.register_page_title')"
    :eyebrow="null"
    :heading="__('ui.auth.register_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="null"
    :aside-text="null"
    :aside-points="[]"
>
    <!-- Global Errors/Status -->
    @if ($errors->any())
        <div class="mb-2 flex w-full flex-col gap-2">
            <div class="text-center text-sm text-rose-300">{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="w-full flex flex-col gap-4">
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
            <label for="password" class="auth-title block text-sm font-medium">{{ __('ui.auth.password_label') }}</label>
            <x-password-input
                id="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="{{ __('ui.auth.password_placeholder') }}"
                input-class="auth-input input w-full rounded-xl px-4 py-3 text-sm transition-all"
            />
        </div>
        
        <div class="space-y-1">
            <label for="password_confirmation" class="auth-title block text-sm font-medium">{{ __('ui.auth.confirm_password_label') }}</label>
            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="{{ __('ui.auth.confirm_password_placeholder') }}"
                input-class="auth-input input w-full rounded-xl px-4 py-3 text-sm transition-all"
            />
        </div>
        
        <button type="submit" class="auth-accent-bg mt-2 w-full rounded-xl px-5 py-3.5 text-sm font-bold transition-all active:scale-95 shadow-[0_16px_30px_-18px_rgba(0,0,0,0.35)]">
            {{ __('ui.auth.register_button') }}
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
                {{ __('ui.auth.already_have_account') }} 
                <a href="{{ route('login') }}" class="auth-link font-semibold transition" data-skip-loader="true">
                    {{ __('ui.auth.sign_in') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

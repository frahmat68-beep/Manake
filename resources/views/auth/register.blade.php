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

        <input
            placeholder="{{ __('ui.auth.email_placeholder') }}"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            class="input w-full rounded-md px-5 py-3 text-sm"
        />

        <x-password-input
            id="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="{{ __('ui.auth.password_placeholder') }}"
            input-class="input w-full rounded-md px-5 py-3 text-sm"
        />
        
        <x-password-input
            id="password_confirmation"
            name="password_confirmation"
            required
            autocomplete="new-password"
            placeholder="{{ __('ui.auth.confirm_password_placeholder') }}"
            input-class="input w-full rounded-md px-5 py-3 text-sm"
        />
        
        <button type="submit" class="btn-primary w-full rounded-md px-5 py-3 text-sm font-medium transition-all active:scale-95">
            {{ __('ui.auth.register_button') }}
        </button>
        
        <x-auth.google-button label="Continue with Google" class="w-full flex items-center justify-center gap-2 rounded-md px-5 py-3 text-sm font-medium transition-all" />
        
        <div class="mt-2 w-full text-center">
            <span class="text-xs text-[#A0A0A8]">
                {{ __('ui.auth.already_have_account') }} 
                <a href="{{ route('login') }}" class="font-medium text-[#D4A843] transition hover:text-[#e0ba5d]" data-skip-loader="true">
                    {{ __('ui.auth.sign_in') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

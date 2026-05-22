<x-guest-layout
    :page-title="__('ui.auth.login_page_title')"
    :eyebrow="null"
    :heading="__('ui.auth.login_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="null"
    :aside-text="null"
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

        <input
            placeholder="{{ __('ui.auth.email_placeholder') }}"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            class="input w-full rounded-md px-5 py-3 text-sm"
        />
        <x-password-input
            id="password"
            name="password"
            required
            placeholder="{{ __('ui.auth.password_placeholder') }}"
            input-class="input w-full rounded-md px-5 py-3 text-sm"
        >
            <a href="{{ route('password.request') }}" class="absolute right-12 top-1/2 -translate-y-1/2 text-xs text-[#A0A0A8] transition-colors hover:text-[#D4A843]" data-skip-loader="true">
                {{ __('ui.auth.forgot_password') }}
            </a>
        </x-password-input>
        
        <button type="submit" class="btn-primary w-full rounded-md px-5 py-3 text-sm font-medium transition-all active:scale-95">
            {{ __('ui.auth.login_button') }}
        </button>
        
        <x-auth.google-button label="Continue with Google" class="w-full flex items-center justify-center gap-2 rounded-md px-5 py-3 text-sm font-medium transition-all" />
        
        <div class="mt-2 w-full text-center">
            <span class="text-xs text-[#A0A0A8]">
                {{ __('ui.auth.no_account') }} 
                <a href="{{ route('register') }}" class="font-medium text-[#D4A843] transition hover:text-[#e0ba5d]" data-skip-loader="true">
                    {{ __('ui.auth.register_now') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

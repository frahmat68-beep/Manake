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

        <div class="space-y-1">
            <label for="email" class="block text-sm font-medium text-[#E8E8EC]">{{ __('Email') }}</label>
            <input
                id="email"
                placeholder="{{ __('ui.auth.email_placeholder') }}"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="input w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-[#E8E8EC] placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all"
            />
        </div>
        
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-[#E8E8EC]">{{ __('Password') }}</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#D4A843] transition-colors hover:text-[#e0ba5d] hover:underline" data-skip-loader="true">
                    {{ __('ui.auth.forgot_password') }}
                </a>
            </div>
            <x-password-input
                id="password"
                name="password"
                required
                placeholder="{{ __('ui.auth.password_placeholder') }}"
                input-class="input w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-[#E8E8EC] placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all"
            >
            </x-password-input>
        </div>
        
        <button type="submit" class="btn-primary mt-2 w-full rounded-xl bg-[#D4A843] px-5 py-3.5 text-sm font-bold text-[#0A0A0B] transition-all hover:bg-[#e0ba5d] active:scale-95 shadow-[0_0_20px_rgba(212,168,67,0.3)]">
            {{ __('ui.auth.login_button') }}
        </button>
        
        <div class="relative mt-2">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-[#1A1A1E]"></div>
            </div>
            <div class="relative flex justify-center text-sm font-medium leading-6">
                <span class="bg-[#0A0A0B] px-6 text-[#A0A0A8]">{{ __('Atau') }}</span>
            </div>
        </div>

        <x-auth.google-button label="Lanjut dengan Google" class="btn-secondary w-full flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-sm font-semibold transition-all hover:bg-[#1A1A1E] border border-[#1A1A1E] text-[#E8E8EC] bg-transparent" />
        
        <div class="mt-4 w-full text-center">
            <span class="text-sm text-[#A0A0A8]">
                {{ __('ui.auth.no_account') }} 
                <a href="{{ route('register') }}" class="font-semibold text-[#D4A843] transition hover:text-[#e0ba5d] hover:underline" data-skip-loader="true">
                    {{ __('ui.auth.register_now') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

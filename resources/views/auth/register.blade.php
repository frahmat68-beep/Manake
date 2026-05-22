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
            <label for="email" class="block text-sm font-medium text-[#E8E8EC]">{{ __('Email') }}</label>
            <input
                id="email"
                placeholder="{{ __('ui.auth.email_placeholder') }}"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="input w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all"
            />
        </div>

        <div class="space-y-1">
            <label for="password" class="block text-sm font-medium text-[#E8E8EC]">{{ __('Password') }}</label>
            <x-password-input
                id="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="{{ __('ui.auth.password_placeholder') }}"
                input-class="input w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all"
            />
        </div>
        
        <div class="space-y-1">
            <label for="password_confirmation" class="block text-sm font-medium text-[#E8E8EC]">{{ __('Konfirmasi Password') }}</label>
            <x-password-input
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="{{ __('ui.auth.confirm_password_placeholder') }}"
                input-class="input w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all"
            />
        </div>
        
        <button type="submit" class="btn-primary mt-2 w-full rounded-xl bg-[#D4A843] px-5 py-3.5 text-sm font-bold text-[#0A0A0B] transition-all hover:bg-[#e0ba5d] active:scale-95 shadow-[0_0_20px_rgba(212,168,67,0.3)]">
            {{ __('ui.auth.register_button') }}
        </button>
        
        <div class="relative mt-2">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-[#1A1A1E]"></div>
            </div>
            <div class="relative flex justify-center text-sm font-medium leading-6">
                <span class="bg-[#0A0A0B] px-6 text-[#A0A0A8]">{{ __('Atau') }}</span>
            </div>
        </div>

        <x-auth.google-button label="Lanjut dengan Google" class="btn-secondary w-full flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-sm font-semibold transition-all hover:bg-[#1A1A1E] border border-[#1A1A1E] text-white bg-transparent" />
        
        <div class="mt-4 w-full text-center">
            <span class="text-sm text-[#A0A0A8]">
                {{ __('ui.auth.already_have_account') }} 
                <a href="{{ route('login') }}" class="font-semibold text-[#D4A843] transition hover:text-[#e0ba5d] hover:underline" data-skip-loader="true">
                    {{ __('ui.auth.sign_in') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

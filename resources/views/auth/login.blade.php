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
        <div class="w-full flex flex-col gap-2 mb-2">
            @if (session('error'))
                <div class="text-sm text-red-400 text-center">{{ session('error') }}</div>
            @endif
            @if (session('status'))
                <div class="text-sm text-emerald-400 text-center">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="text-sm text-red-400 text-center">{{ $errors->first() }}</div>
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
            class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        />
        <x-password-input
            id="password"
            name="password"
            required
            placeholder="{{ __('ui.auth.password_placeholder') }}"
            input-class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        >
            <a href="{{ route('password.request') }}" class="absolute right-12 top-1/2 -translate-y-1/2 text-xs text-gray-500 hover:text-white transition-colors" data-skip-loader="true">
                {{ __('ui.auth.forgot_password') }}
            </a>
        </x-password-input>
        
        <button type="submit" class="w-full bg-blue-600 !text-white font-medium px-5 py-3 rounded-xl shadow-[0_4px_20px_-5px_rgba(37,99,235,0.5)] hover:bg-blue-500 transition-all active:scale-95 mb-1 text-sm mt-2">
            {{ __('ui.auth.login_button') }}
        </button>
        
        <x-auth.google-button label="Continue with Google" class="w-full flex items-center justify-center gap-2 !bg-white/5 !rounded-xl !px-5 !py-3 !font-medium !text-white !border !border-white/10 hover:!bg-white/10 transition-all !text-sm mb-2" />
        
        <div class="w-full text-center mt-2">
            <span class="text-xs text-gray-400">
                {{ __('ui.auth.no_account') }} 
                <a href="{{ route('register') }}" class="font-medium text-blue-500 hover:text-blue-400 transition" data-skip-loader="true">
                    {{ __('ui.auth.register_now') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

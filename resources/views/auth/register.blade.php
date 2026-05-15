<x-guest-layout
    :page-title="__('app.auth.register_page_title')"
    :eyebrow="null"
    :heading="__('app.auth.register_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="null"
    :aside-text="null"
    :aside-points="[]"
>
    <!-- Global Errors/Status -->
    @if ($errors->any())
        <div class="w-full flex flex-col gap-2 mb-2">
            <div class="text-sm text-red-400 text-center">{{ $errors->first() }}</div>
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
            class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        />

        <x-password-input
            id="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="{{ __('ui.auth.password_placeholder') }}"
            input-class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        />
        
        <x-password-input
            id="password_confirmation"
            name="password_confirmation"
            required
            autocomplete="new-password"
            placeholder="{{ __('ui.auth.confirm_password_placeholder') }}"
            input-class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        />
        
        <button type="submit" class="w-full bg-blue-600 !text-white font-medium px-5 py-3 rounded-xl shadow-[0_4px_20px_-5px_rgba(37,99,235,0.5)] hover:bg-blue-500 transition-all active:scale-95 mb-1 text-sm mt-2">
            Sign up
        </button>
        
        <x-auth.google-button label="Continue with Google" class="w-full flex items-center justify-center gap-2 !bg-white/5 !rounded-xl !px-5 !py-3 !font-medium !text-white !border !border-white/10 hover:!bg-white/10 transition-all !text-sm mb-2" />
        
        <div class="w-full text-center mt-2">
            <span class="text-xs text-gray-400">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-medium text-blue-500 hover:text-blue-400 transition" data-skip-loader="true">
                    Sign in
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

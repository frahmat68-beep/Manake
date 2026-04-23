<x-guest-layout
    :page-title="__('ui.auth.forgot_title') . ' | Manake.Id'"
    :eyebrow="null"
    :heading="__('ui.auth.forgot_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="null"
    :aside-text="null"
    :aside-points="[]"
>
    <!-- Global Errors/Status -->
    @if (session('status') || $errors->any())
        <div class="w-full flex flex-col gap-2 mb-2">
            @if (session('status'))
                <div class="text-sm text-emerald-400 text-center">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="text-sm text-red-400 text-center">{{ $errors->first() }}</div>
            @endif
        </div>
    @endif

    <div class="w-full text-center mb-4">
        <p class="text-sm text-gray-400">{{ __('ui.auth.forgot_help') }}</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="w-full flex flex-col gap-4">
        @csrf

        <input
            placeholder="{{ __('ui.auth.email_placeholder') }}"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            class="w-full px-5 py-3 rounded-xl !bg-[#18181b] !text-white !border !border-white/5 placeholder:text-gray-500 text-sm focus:outline-none focus:!border-blue-500 focus:!ring-4 focus:!ring-blue-600/20 transition-all"
        />

        <button type="submit" class="w-full bg-blue-600 !text-white font-medium px-5 py-3 rounded-xl shadow-[0_4px_20px_-5px_rgba(37,99,235,0.5)] hover:bg-blue-500 transition-all active:scale-95 mb-1 text-sm mt-2">
            {{ __('ui.auth.forgot_button') }}
        </button>

        <div class="w-full text-center mt-2">
            <span class="text-xs text-gray-400">
                <a href="{{ route('login') }}" class="font-medium text-blue-500 hover:text-blue-400 transition-colors" data-skip-loader="true">
                    &larr; {{ __('ui.auth.back_to_login') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

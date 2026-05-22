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
        <p class="text-sm text-[#A0A0A8]">{{ __('ui.auth.forgot_help') }}</p>
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
            class="w-full rounded-md border border-[#1A1A1E] bg-[#0A0A0B] px-5 py-3 text-sm text-[#E8E8EC] placeholder:text-[#66666C] focus:border-[#D4A843] focus:ring-4 focus:ring-[#D4A843]/20 focus:outline-none transition-all"
        />

        <button type="submit" class="w-full bg-[#D4A843] text-[#0A0A0B] font-medium px-5 py-3 rounded-md shadow-[0_4px_20px_-5px_rgba(212,168,67,0.35)] hover:bg-[#e0ba5d] transition-all active:scale-95 mb-1 text-sm mt-2">
            {{ __('ui.auth.forgot_button') }}
        </button>

        <div class="w-full text-center mt-2">
            <span class="text-xs text-[#A0A0A8]">
                <a href="{{ route('login') }}" class="font-medium text-[#D4A843] hover:text-[#e0ba5d] transition-colors" data-skip-loader="true">
                    &larr; {{ __('ui.auth.back_to_login') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

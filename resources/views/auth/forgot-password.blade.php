<x-guest-layout
    :page-title="__('ui.auth.forgot_title') . ' | Manake'"
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

    <div class="auth-forgot-intro w-full text-center">
        <p class="auth-muted text-sm">{{ __('ui.auth.forgot_help') }}</p>
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
            class="auth-input w-full rounded-xl px-5 py-3 text-sm transition-all"
        />

        <button type="submit" class="auth-accent-bg mt-2 mb-1 w-full rounded-xl px-5 py-3 text-sm font-medium transition-all active:scale-95 shadow-[0_16px_30px_-18px_rgba(0,0,0,0.35)]">
            {{ __('ui.auth.forgot_button') }}
        </button>

        <div class="w-full text-center mt-2">
            <span class="auth-muted text-xs">
                <a href="{{ route('login') }}" class="auth-link font-medium transition-colors" data-skip-loader="true">
                    &larr; {{ __('ui.auth.back_to_login') }}
                </a>
            </span>
        </div>
    </form>
</x-guest-layout>

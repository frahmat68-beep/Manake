<x-guest-layout
    :page-title="__('ui.auth.forgot_title') . ' | Manake.Id'"
    :eyebrow="null"
    :heading="__('ui.auth.forgot_title')"
    :subheading="__('ui.auth.forgot_help')"
    :aside-eyebrow="null"
    :aside-heading="__('ui.auth.forgot_title')"
    :aside-text="__('ui.auth.forgot_note')"
    :aside-points="[]"
>
    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="forgot-email" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('ui.auth.email_label') }}
                </label>
                <input
                    id="forgot-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                    placeholder="{{ __('ui.auth.email_placeholder') }}"
                >
                @error('email')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('ui.auth.forgot_button') }}
            </button>
        </form>

        <div class="border-t border-slate-200/80 pt-4 text-sm text-slate-500">
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700" data-skip-loader="true">
                {{ __('ui.auth.back_to_login') }}
            </a>
        </div>
    </div>
</x-guest-layout>

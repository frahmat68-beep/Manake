<x-guest-layout
    :page-title="__('app.auth.register_page_title')"
    :eyebrow="null"
    :heading="__('app.auth.register_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="__('app.auth.register_step_1_desc')"
    :aside-text="null"
    :aside-points="[]"
>
    <div class="space-y-4">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="register-email" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.email') }}
                </label>
                <input
                    id="register-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                    placeholder="{{ __('app.auth.email_placeholder') }}"
                >
            </div>

            <div class="space-y-1.5">
                <label for="register-password" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.password') }}
                </label>
                <x-password-input
                    id="register-password"
                    name="password"
                    :required="true"
                    placeholder="{{ __('app.auth.password_placeholder') }}"
                    autocomplete="new-password"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
            </div>

            <div class="space-y-1.5">
                <label for="register-password-confirmation" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.password_confirm') }}
                </label>
                <x-password-input
                    id="register-password-confirmation"
                    name="password_confirmation"
                    :required="true"
                    placeholder="{{ __('app.auth.password_confirm_placeholder') }}"
                    autocomplete="new-password"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.register_button') }}
            </button>
        </form>

        <div class="border-t border-slate-200/80 pt-4 text-sm text-slate-500">
            {{ __('app.auth.already_have_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700" data-skip-loader="true">
                {{ __('app.auth.login_link') }}
            </a>
        </div>
    </div>
</x-guest-layout>

<x-guest-layout
    :page-title="__('app.auth.login_page_title')"
    :eyebrow="null"
    :heading="__('app.auth.login_title')"
    :subheading="__('app.auth.login_note')"
    :aside-eyebrow="null"
    :aside-heading="__('app.auth.login_benefit_1')"
    :aside-text="__('app.auth.login_benefit_2')"
    :aside-points="[]"
>
    <div class="space-y-4">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('error') }}
            </div>
        @endif

        @if (session('status'))
            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="login-email" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.email') }}
                </label>
                <input
                    id="login-email"
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
                <div class="flex items-center justify-between gap-3">
                    <label for="login-password" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                        {{ __('app.auth.password') }}
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700" data-skip-loader="true">
                        {{ __('app.auth.forgot_password') }}
                    </a>
                </div>
                <x-password-input
                    id="login-password"
                    name="password"
                    :required="true"
                    placeholder="{{ __('app.auth.password_placeholder_mask') }}"
                    autocomplete="current-password"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.login_button') }}
            </button>
        </form>

        <div class="border-t border-slate-200/80 pt-4 text-sm text-slate-500">
            {{ __('app.auth.no_account') }}
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700" data-skip-loader="true">
                {{ __('app.auth.register_now') }}
            </a>
        </div>
    </div>
</x-guest-layout>

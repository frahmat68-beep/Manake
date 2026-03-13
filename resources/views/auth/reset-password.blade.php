<x-guest-layout
    :page-title="__('app.auth.reset_password_button') . ' | Manake.Id'"
    :eyebrow="null"
    :heading="__('app.auth.reset_password_button')"
    :subheading="__('ui.auth.forgot_help')"
    :aside-eyebrow="null"
    :aside-heading="__('app.auth.reset_password_button')"
    :aside-text="__('ui.auth.forgot_note')"
    :aside-points="[]"
>
    <div class="space-y-5">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="space-y-1.5">
                <label for="reset-email" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.email') }}
                </label>
                <input
                    id="reset-email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                    placeholder="{{ __('app.auth.email_placeholder') }}"
                >
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="space-y-1.5">
                <label for="reset-password" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.password') }}
                </label>
                <x-password-input
                    id="reset-password"
                    name="password"
                    :required="true"
                    autocomplete="new-password"
                    placeholder="{{ __('app.auth.password_placeholder') }}"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="space-y-1.5">
                <label for="reset-password-confirmation" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.password_confirm') }}
                </label>
                <x-password-input
                    id="reset-password-confirmation"
                    name="password_confirmation"
                    :required="true"
                    autocomplete="new-password"
                    placeholder="{{ __('app.auth.password_confirm_placeholder') }}"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.reset_password_button') }}
            </button>
        </form>
    </div>
</x-guest-layout>

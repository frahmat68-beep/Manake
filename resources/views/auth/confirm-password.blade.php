<x-guest-layout
    :page-title="__('app.auth.confirm_button') . ' | Manake.Id'"
    :eyebrow="__('app.auth.confirm_button')"
    :heading="__('app.auth.confirm_button')"
    :subheading="__('app.auth.confirm_password_intro')"
    :aside-eyebrow="__('ui.admin.panel_title')"
    :aside-heading="__('app.auth.confirm_button')"
    :aside-text="__('app.auth.confirm_password_intro')"
    :aside-points="[__('app.auth.password')]"
>
    <div class="space-y-5">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="confirm-password" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('app.auth.password') }}
                </label>
                <x-password-input
                    id="confirm-password"
                    name="password"
                    :required="true"
                    autocomplete="current-password"
                    placeholder="{{ __('app.auth.password_placeholder_mask') }}"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.confirm_button') }}
            </button>
        </form>
    </div>
</x-guest-layout>

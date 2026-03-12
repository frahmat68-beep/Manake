<x-guest-layout
    :page-title="__('ui.admin.admin_login') . ' | Manake.Id'"
    :eyebrow="__('ui.admin.panel_title')"
    :heading="__('ui.admin.login_heading')"
    :subheading="__('ui.admin.login_intro')"
    :aside-eyebrow="__('ui.admin.sidebar_operational')"
    :aside-heading="__('ui.admin.panel_title')"
    :aside-text="__('ui.admin.login_subheading')"
    :aside-points="[
        __('ui.admin.equipments'),
        __('ui.admin.orders'),
        __('ui.admin.website_settings'),
    ]"
>
    <div class="space-y-5">
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="admin-email" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('ui.admin.email') }}
                </label>
                <input
                    id="admin-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="input w-full rounded-2xl px-4 py-3 text-sm"
                    placeholder="admin@manake.id"
                >
            </div>

            <div class="space-y-1.5">
                <label for="admin-auth-password" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    {{ __('ui.admin.password') }}
                </label>
                <x-password-input
                    id="admin-auth-password"
                    name="password"
                    :required="true"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    input-class="input w-full rounded-2xl px-4 py-3 text-sm"
                />
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('ui.admin.login_button') }}
            </button>
        </form>

        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
            {{ __('ui.admin.login_hint') }}
        </div>
    </div>
</x-guest-layout>

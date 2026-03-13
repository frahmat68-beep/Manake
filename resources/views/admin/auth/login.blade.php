<x-guest-layout
    :page-title="__('ui.admin.admin_login') . ' | Manake.Id'"
    :eyebrow="null"
    :heading="__('ui.admin.admin_login')"
    :subheading="__('ui.admin.login_hint')"
    :aside-eyebrow="null"
    :aside-heading="__('ui.admin.login_subheading')"
    :aside-text="null"
    :aside-points="[]"
>
    <div class="space-y-4">
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

        <div class="border-t border-slate-200/80 pt-4 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="font-semibold text-blue-600 hover:text-blue-700" data-skip-loader="true">
                {{ __('app.auth.back_home') }}
            </a>
        </div>
    </div>
</x-guest-layout>

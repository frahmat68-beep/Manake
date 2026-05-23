<x-guest-layout
    :page-title="__('ui.admin.admin_login') . ' | Manake'"
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
                <label for="admin-email" class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#A0A0A8]">
                    {{ __('ui.admin.email') }}
                </label>
                <input
                    id="admin-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="input w-full rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843]"
                    placeholder="admin@manake.id"
                >
            </div>

            <div class="space-y-1.5">
                <label for="admin-auth-password" class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#A0A0A8]">
                    {{ __('ui.admin.password') }}
                </label>
                <x-password-input
                    id="admin-auth-password"
                    name="password"
                    :required="true"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    input-class="input w-full rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843]"
                />
            </div>

            <button type="submit" class="btn-primary inline-flex w-full items-center justify-center rounded-2xl bg-[#D4A843] px-4 py-3 text-sm font-extrabold text-[#0A0A0B] shadow-[0_0_20px_rgba(212,168,67,0.24)] transition hover:bg-[#e0ba5d] active:scale-95">
                {{ __('ui.admin.login_button') }}
            </button>
        </form>

        <div class="border-t border-[#1A1A1E] pt-4 text-sm text-[#A0A0A8]">
            <a href="{{ route('home') }}" class="font-semibold text-[#D4A843] transition hover:text-[#e0ba5d]" data-skip-loader="true">
                {{ __('app.auth.back_home') }}
            </a>
        </div>
    </div>
</x-guest-layout>

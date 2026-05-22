<x-guest-layout
    :page-title="__('app.auth.otp_page_title')"
    :eyebrow="null"
    :heading="__('app.auth.otp_title')"
    :subheading="null"
    :aside-eyebrow="null"
    :aside-heading="__('app.auth.otp_point_1')"
    :aside-text="null"
    :aside-points="[]"
>
    <div class="space-y-4">
        @if (session('status'))
            <div class="mk-card border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mk-card border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="otp" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#A0A0A8]">
                    {{ __('app.auth.otp_label') }}
                </label>
                <input
                    id="otp"
                    name="otp"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="{{ __('app.auth.otp_placeholder') }}"
                    class="input w-full rounded-2xl px-4 py-3 text-center text-2xl tracking-[0.35em]"
                    required
                >
            </div>

            <button class="btn-primary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.otp_verify_button') }}
            </button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}">
            @csrf
            <button class="btn-secondary inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold">
                {{ __('app.auth.otp_resend_button') }}
            </button>
        </form>
    </div>
</x-guest-layout>

<x-guest-layout
    :page-title="__('app.auth.otp_page_title')"
    :eyebrow="__('app.auth.otp_title')"
    :heading="__('app.auth.otp_heading')"
    :subheading="__('app.auth.otp_subheading')"
    :aside-eyebrow="__('app.auth.otp_title')"
    :aside-heading="__('app.auth.otp_aside_heading')"
    :aside-text="__('app.auth.otp_aside_text')"
    :aside-points="[
        __('app.auth.otp_point_1'),
        __('app.auth.otp_point_2'),
        __('app.auth.otp_point_3'),
    ]"
>
    <div class="space-y-5">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="otp" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
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

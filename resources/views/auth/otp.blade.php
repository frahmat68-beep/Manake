<x-guest-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ __('Verifikasi OTP') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('Masukkan 6 digit kode OTP yang dikirim ke email kamu.') }}</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="otp" class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Kode OTP') }}</label>
                <input
                    id="otp"
                    name="otp"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="{{ __('Contoh: 123456') }}"
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-lg tracking-[0.35em] text-slate-800 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    required
                >
            </div>
            <button class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                {{ __('Verifikasi OTP') }}
            </button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}" class="text-center">
            @csrf
            <button class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                {{ __('Kirim ulang OTP') }}
            </button>
        </form>
    </div>
</x-guest-layout>

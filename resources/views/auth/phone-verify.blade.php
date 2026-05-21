@extends('layouts.app')

@section('title', __('Verifikasi Nomor Telepon'))

@section('content')
    <section class="bg-slate-50">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
            <div class="mk-card p-6 sm:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">{{ __('Verifikasi Telepon') }}</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ __('Verifikasi Nomor Telepon') }}</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{ __('Pembayaran hanya bisa diproses setelah nomor telepon terverifikasi. OTP dikirim via driver log (mode pengembangan).') }}
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <form method="POST" action="{{ route('phone.otp.request') }}" class="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        @csrf
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ __('1) Minta OTP') }}</h2>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Nomor Telepon') }}</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $profile?->phone ?? '') }}"
                            placeholder="{{ __('08xxxxxxxxxx') }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('phone') border-rose-400 focus:border-rose-400 focus:ring-rose-200/70 @enderror"
                        >
                        @error('phone')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <button class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                            {{ __('Kirim OTP') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('phone.otp.verify') }}" class="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        @csrf
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">{{ __('2) Verifikasi OTP') }}</h2>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Kode OTP') }}</label>
                        <input
                            type="text"
                            name="otp"
                            maxlength="6"
                            value="{{ old('otp') }}"
                            placeholder="{{ __('6 digit OTP') }}"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('otp') border-rose-400 focus:border-rose-400 focus:ring-rose-200/70 @enderror"
                        >
                        @error('otp')
                            <p class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <button class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                            {{ __('Verifikasi') }}
                        </button>
                    </form>
                </div>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400">
                    {{ __('Status saat ini:') }}
                    @if ($profile?->phone_verified_at)
                        <span class="font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Terverifikasi') }} ({{ $profile->phone_verified_at->format('d M Y H:i') }})</span>
                    @else
                        <span class="font-semibold text-amber-700 dark:text-amber-300">{{ __('Belum terverifikasi') }}</span>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('profile.complete') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:text-slate-300 dark:hover:border-blue-500/40 dark:hover:text-blue-300">
                        {{ __('Kembali ke Profil') }}
                    </a>
                    <a href="{{ route('checkout') }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ __('Coba Lanjut Pembayaran') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

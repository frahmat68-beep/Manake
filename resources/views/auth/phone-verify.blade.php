@extends('layouts.app')

@section('title', __('Verifikasi Nomor Telepon'))

@section('content')
    <section class="bg-[#0A0A0B] text-[#E8E8EC]">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
            <div class="mk-card p-6 sm:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#D4A843]">{{ __('Verifikasi Telepon') }}</p>
                <h1 class="mt-2 text-2xl font-semibold text-[#E8E8EC]">{{ __('Verifikasi Nomor Telepon') }}</h1>
                <p class="mt-2 text-sm text-[#A0A0A8]">
                    {{ __('Pembayaran hanya bisa diproses setelah nomor telepon terverifikasi. OTP dikirim via driver log (mode pengembangan).') }}
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-950/30 px-4 py-3 text-sm text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mt-4 rounded-xl border border-emerald-500/20 bg-emerald-950/30 px-4 py-3 text-sm text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <form method="POST" action="{{ route('phone.otp.request') }}" class="space-y-3 rounded-xl border border-[#1A1A1E] bg-[#111113] p-4">
                        @csrf
                        <h2 class="text-sm font-semibold text-[#E8E8EC]">{{ __('1) Minta OTP') }}</h2>
                        <label class="block text-xs font-semibold text-[#A0A0A8]">{{ __('Nomor Telepon') }}</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $profile?->phone ?? '') }}"
                            placeholder="{{ __('08xxxxxxxxxx') }}"
                            class="w-full rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-2 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/20 focus:outline-none @error('phone') border-rose-400 focus:border-rose-400 focus:ring-rose-200/70 @enderror"
                        >
                        @error('phone')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                        <button class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-4 py-2.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                            {{ __('Kirim OTP') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('phone.otp.verify') }}" class="space-y-3 rounded-xl border border-[#1A1A1E] bg-[#111113] p-4">
                        @csrf
                        <h2 class="text-sm font-semibold text-[#E8E8EC]">{{ __('2) Verifikasi OTP') }}</h2>
                        <label class="block text-xs font-semibold text-[#A0A0A8]">{{ __('Kode OTP') }}</label>
                        <input
                            type="text"
                            name="otp"
                            maxlength="6"
                            value="{{ old('otp') }}"
                            placeholder="{{ __('6 digit OTP') }}"
                            class="w-full rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 py-2 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-2 focus:ring-[#D4A843]/20 focus:outline-none @error('otp') border-rose-400 focus:border-rose-400 focus:ring-rose-200/70 @enderror"
                        >
                        @error('otp')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                        <button class="inline-flex w-full items-center justify-center rounded-xl border border-[#1A1A1E] px-4 py-2.5 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                            {{ __('Verifikasi') }}
                        </button>
                    </form>
                </div>

                <div class="mt-4 rounded-xl border border-[#1A1A1E] bg-[#111113] px-4 py-3 text-xs text-[#A0A0A8]">
                    {{ __('Status saat ini:') }}
                    @if ($profile?->phone_verified_at)
                        <span class="font-semibold text-emerald-300">{{ __('Terverifikasi') }} ({{ $profile->phone_verified_at->format('d M Y H:i') }})</span>
                    @else
                        <span class="font-semibold text-amber-300">{{ __('Belum terverifikasi') }}</span>
                    @endif
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('profile.complete') }}" class="inline-flex items-center rounded-xl border border-[#1A1A1E] px-4 py-2 text-sm font-semibold text-[#E8E8EC] hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                        {{ __('Kembali ke Profil') }}
                    </a>
                    <a href="{{ route('checkout') }}" class="inline-flex items-center rounded-xl bg-[#D4A843] px-4 py-2 text-sm font-semibold text-[#0A0A0B] hover:bg-[#e0ba5d]">
                        {{ __('Coba Lanjut Pembayaran') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

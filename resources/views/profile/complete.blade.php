@extends('layouts.app')

@section('title', __('ui.profile_complete.page_title'))

@php
    $safeMapsUrl = trusted_map_embed_url((string) ($profile?->maps_url ?? ''), $profile?->address_text ?? null);
    $profileComplete = (bool) ($user?->hasCompleteRentalProfile() ?? false);
    $emailVerified = (bool) ($user?->hasVerifiedEmail() ?? false);
    $phoneVerified = (bool) ($user?->hasVerifiedPhone() ?? false);
    $allReady = $profileComplete && $emailVerified && $phoneVerified;
    $hasSavedProfile = (bool) (($profile?->exists ?? false) && array_filter([
        $profile?->full_name ?? $user?->name,
        $profile?->nik ?? $profile?->identity_number,
        $profile?->phone,
        $profile?->address_line ?? $profile?->address,
        $profile?->city,
    ]));
    $hasLockedFullName = trim((string) ($profile?->full_name ?? '')) !== '';
    $hasLockedNik = preg_match('/^\d{16}$/', preg_replace('/[^0-9]/', '', (string) ($profile?->nik ?? ''))) === 1;
    $statusMessage = session('status');
    $successMessage = session('success');
    $warningMessage = session('warning');
    $errorMessage = session('error');

    $statusBadge = static function (bool $done, string $label, string $pendingLabel = 'Belum selesai') {
        $tone = $done
            ? 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200'
            : 'border-amber-400/20 bg-amber-500/10 text-amber-200';

        return [
            'tone' => $tone,
            'label' => $done ? 'Selesai' : $pendingLabel,
            'name' => $label,
        ];
    };

    $profileStatus = $statusBadge($profileComplete, 'Profil Lengkap');
    $emailStatus = $statusBadge($emailVerified, 'Email Terverifikasi', 'Perlu verifikasi');
    $phoneStatus = $statusBadge($phoneVerified, 'Telepon Terverifikasi', 'Perlu verifikasi');
@endphp

@push('head')
    <style>
        .profile-shell-enter {
            animation: profile-shell-enter 520ms ease-out both;
        }

        .profile-card-in {
            animation: profile-card-in 520ms ease-out both;
        }

        @keyframes profile-shell-enter {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes profile-card-in {
            from {
                opacity: 0;
                transform: translateY(14px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .profile-shell-enter,
            .profile-card-in {
                animation: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <section class="bg-[#0A0A0B]">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10">
            <header class="profile-shell-enter rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-8">
                <div class="space-y-5">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold tracking-[0.18em] text-[#D4A843]">{{ __('Lengkapi Profil') }}</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h1 class="text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">{{ __('Data Profil Penyewaan') }}</h1>
                            @if ($hasSavedProfile)
                                <span class="inline-flex w-fit items-center gap-2 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-200">
                                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-300"></span>
                                    {{ __('ui.profile_complete.saved_badge') }}
                                </span>
                            @endif
                        </div>
                        <p class="max-w-3xl text-sm leading-6 text-[#A0A0A8] sm:text-base">
                            {{ __('Lengkapi identitas dan kontak agar proses booking dan pembayaran bisa diverifikasi.') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        @foreach ([$profileStatus, $emailStatus, $phoneStatus] as $chip)
                            <div class="inline-flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $chip['tone'] }}">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-current/15">
                                    @if ($chip['label'] === 'Selesai')
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M10.29 3.86l-7.18 12.42A2 2 0 004.82 19h14.36a2 2 0 001.71-2.72L13.71 3.86a2 2 0 00-3.42 0z" />
                                        </svg>
                                    @endif
                                </span>
                                <div>
                                    <p class="text-sm font-semibold">{{ __($chip['name']) }}</p>
                                    <p class="text-xs opacity-85">{{ __($chip['label']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </header>

            @if ($profilesTableMissing ?? false)
                <div class="mt-5 rounded-2xl border border-amber-400/20 bg-amber-500/8 px-4 py-3 text-sm text-amber-200">
                    {{ __('Profil belum siap. Jalankan migrasi database terlebih dahulu.') }}
                </div>
            @endif

            @if ($statusMessage || $successMessage || $warningMessage || $errorMessage || $errors->any())
                <div class="mt-5 space-y-3">
                    @if ($statusMessage)
                        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/8 px-4 py-3 text-sm text-emerald-200">
                            {{ is_string($statusMessage) ? __($statusMessage) : $statusMessage }}
                        </div>
                    @endif
                    @if ($successMessage)
                        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/8 px-4 py-3 text-sm text-emerald-200">
                            {{ $successMessage }}
                        </div>
                    @endif
                    @if ($warningMessage)
                        <div class="rounded-2xl border border-amber-400/20 bg-amber-500/8 px-4 py-3 text-sm text-amber-200">
                            {{ $warningMessage }}
                        </div>
                    @endif
                    @if ($errorMessage)
                        <div class="rounded-2xl border border-rose-400/20 bg-rose-500/8 px-4 py-3 text-sm text-rose-200">
                            {{ $errorMessage }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="rounded-2xl border border-rose-400/20 bg-rose-500/8 px-4 py-3 text-sm text-rose-200">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)] lg:items-start">
                <form method="POST" action="{{ route('profile.store') }}" class="space-y-6">
                    @csrf

                    <article class="profile-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Identitas') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('Pastikan data identitas sesuai dokumen penyewaan.') }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="full_name" class="text-sm font-medium text-[#E8E8EC]">{{ __('Nama Lengkap') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $profile->full_name ?? $user?->name) }}" @if ($hasLockedFullName) readonly @endif required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @if($hasLockedFullName) cursor-not-allowed bg-[#171719] text-[#A0A0A8] @endif @error('full_name') border-rose-400 @enderror">
                                @error('full_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="nik" class="text-sm font-medium text-[#E8E8EC]">{{ __('NIK') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="nik" type="text" name="nik" value="{{ old('nik', $profile->nik ?? $profile->identity_number) }}" @if ($hasLockedNik) readonly @endif required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @if($hasLockedNik) cursor-not-allowed bg-[#171719] text-[#A0A0A8] @endif @error('nik') border-rose-400 @enderror">
                                <p class="mt-1 text-xs text-[#A0A0A8]">{{ __('Digunakan untuk verifikasi penyewaan.') }}</p>
                                @error('nik')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="date_of_birth" class="text-sm font-medium text-[#E8E8EC]">{{ __('Tanggal Lahir') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('date_of_birth') border-rose-400 @enderror">
                                @error('date_of_birth')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="gender" class="text-sm font-medium text-[#E8E8EC]">{{ __('Gender') }} <span class="text-[#D4A843]">*</span></label>
                                <select id="gender" name="gender" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('gender') border-rose-400 @enderror">
                                    <option value="" disabled @selected(! in_array(old('gender', $profile->gender ?? ''), ['male', 'female'], true))>{{ __('Pilih gender') }}</option>
                                    <option value="male" @selected(old('gender', $profile->gender ?? '') === 'male')>{{ __('Laki-laki') }}</option>
                                    <option value="female" @selected(old('gender', $profile->gender ?? '') === 'female')>{{ __('Perempuan') }}</option>
                                </select>
                                @error('gender')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <article class="profile-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8" style="animation-delay: 70ms">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Kontak & Alamat') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('Gunakan nomor aktif dan alamat penanggung jawab penyewaan.') }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="phone" class="text-sm font-medium text-[#E8E8EC]">{{ __('Nomor Telepon') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('phone') border-rose-400 @enderror">
                                <p class="mt-1 text-xs text-[#A0A0A8]">{{ __('Nomor ini akan digunakan untuk OTP dan koordinasi pengambilan.') }}</p>
                                @error('phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="maps_url" class="text-sm font-medium text-[#E8E8EC]">{{ __('Google Maps Link (opsional)') }}</label>
                                <input id="maps_url" type="url" name="maps_url" value="{{ old('maps_url', $profile->maps_url ?? '') }}" class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('maps_url') border-rose-400 @enderror">
                                @error('maps_url')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address_line" class="text-sm font-medium text-[#E8E8EC]">{{ __('Alamat Lengkap') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="address_line" type="text" name="address_line" value="{{ old('address_line', $profile->address_line ?? $profile->address ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('address_line') border-rose-400 @enderror">
                                <p class="mt-1 text-xs text-[#A0A0A8]">{{ __('Isi alamat domisili atau alamat penanggung jawab.') }}</p>
                                @error('address_line')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="kelurahan" class="text-sm font-medium text-[#E8E8EC]">{{ __('Kelurahan') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="kelurahan" type="text" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('kelurahan') border-rose-400 @enderror">
                                @error('kelurahan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="kecamatan" class="text-sm font-medium text-[#E8E8EC]">{{ __('Kecamatan') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="kecamatan" type="text" name="kecamatan" value="{{ old('kecamatan', $profile->kecamatan ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('kecamatan') border-rose-400 @enderror">
                                @error('kecamatan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="city" class="text-sm font-medium text-[#E8E8EC]">{{ __('Kota / Kabupaten') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="city" type="text" name="city" value="{{ old('city', $profile->city ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('city') border-rose-400 @enderror">
                                @error('city')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="province" class="text-sm font-medium text-[#E8E8EC]">{{ __('Provinsi') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="province" type="text" name="province" value="{{ old('province', $profile->province ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('province') border-rose-400 @enderror">
                                @error('province')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="postal_code" class="text-sm font-medium text-[#E8E8EC]">{{ __('Kode Pos') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('postal_code') border-rose-400 @enderror">
                                @error('postal_code')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <article class="profile-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6 sm:p-8" style="animation-delay: 140ms">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Kontak Darurat') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('Kontak ini dipakai bila ada kebutuhan konfirmasi cepat.') }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div>
                                <label for="emergency_name" class="text-sm font-medium text-[#E8E8EC]">{{ __('Nama') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="emergency_name" type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('emergency_name') border-rose-400 @enderror">
                                @error('emergency_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="emergency_relation" class="text-sm font-medium text-[#E8E8EC]">{{ __('Hubungan') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="emergency_relation" type="text" name="emergency_relation" value="{{ old('emergency_relation', $profile->emergency_relation ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('emergency_relation') border-rose-400 @enderror">
                                @error('emergency_relation')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="emergency_phone" class="text-sm font-medium text-[#E8E8EC]">{{ __('No. Telepon') }} <span class="text-[#D4A843]">*</span></label>
                                <input id="emergency_phone" type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" required class="input mt-2 w-full rounded-xl border border-white/10 bg-[#0A0A0B]/80 px-4 py-3 text-sm text-[#E8E8EC] focus:border-[#D4A843] focus:ring-1 focus:ring-[#D4A843] @error('emergency_phone') border-rose-400 @enderror">
                                @error('emergency_phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <div class="profile-card-in flex flex-col gap-3 sm:flex-row" style="animation-delay: 210ms">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40">
                            {{ __('Simpan Profil') }}
                        </button>
                        <a href="{{ route('catalog') }}" class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-6 py-3.5 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]">
                            {{ __('Kembali ke Katalog') }}
                        </a>
                    </div>
                </form>

                <aside class="space-y-6">
                    <article class="profile-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6" style="animation-delay: 60ms">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight text-[#E8E8EC]">{{ __('Syarat Bisa Memesan') }}</h2>
                            <p class="text-sm text-[#A0A0A8]">{{ __('Lengkapi status berikut sebelum menambahkan alat ke keranjang atau checkout.') }}</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ([$profileStatus, $emailStatus, $phoneStatus] as $item)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/75 px-4 py-3">
                                    <span class="text-sm font-medium text-[#E8E8EC]">{{ __($item['name']) }}</span>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $item['tone'] }}">
                                        {{ __($item['label']) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/75 p-4">
                            @if (! $profileComplete)
                                <p class="text-sm leading-6 text-[#A0A0A8]">{{ __('Lengkapi dan simpan data profil terlebih dahulu.') }}</p>
                            @elseif (! $emailVerified)
                                <p class="text-sm leading-6 text-[#A0A0A8]">{{ __('Verifikasi email terlebih dahulu sebelum melanjutkan ke OTP nomor telepon.') }}</p>
                            @elseif (! $phoneVerified)
                                <p class="text-sm leading-6 text-[#A0A0A8]">{{ __('Profil tersimpan. Lanjutkan verifikasi nomor telepon sebelum memesan.') }}</p>
                            @else
                                <p class="text-sm leading-6 text-emerald-200">{{ __('Profil siap digunakan untuk pemesanan.') }}</p>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            @if (! $emailVerified)
                                <form method="POST" action="{{ route('verification.send') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]">
                                        {{ __('Verifikasi Email') }}
                                    </button>
                                </form>
                            @endif

                            @if ($emailVerified && ! $phoneVerified)
                                <a href="{{ route('phone.verify') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-4 py-3 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                    {{ __('Verifikasi Nomor Telepon') }}
                                </a>
                            @endif

                            @if ($allReady)
                                <a href="{{ route('catalog') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-4 py-3 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                    {{ __('Mulai Memesan Alat') }}
                                </a>
                            @endif
                        </div>
                    </article>

                    <article class="profile-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-6" style="animation-delay: 120ms">
                        <h3 class="text-base font-semibold text-[#E8E8EC]">{{ __('Ringkasan Kontak') }}</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#A0A0A8]">{{ __('Email') }}</dt>
                                <dd class="text-right font-medium text-[#E8E8EC]">{{ $user?->email ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#A0A0A8]">{{ __('Telepon') }}</dt>
                                <dd class="text-right font-medium text-[#E8E8EC]">{{ $profile?->phone ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#A0A0A8]">{{ __('Maps') }}</dt>
                                <dd class="text-right font-medium text-[#E8E8EC]">
                                    @if ($safeMapsUrl)
                                        <a href="{{ $safeMapsUrl }}" target="_blank" rel="noopener noreferrer" class="text-[#D4A843] hover:text-[#e0ba5d]">{{ __('Buka Link') }}</a>
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </article>
                </aside>
            </div>
        </div>
    </section>
@endsection

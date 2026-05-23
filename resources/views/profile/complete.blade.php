@extends('layouts.app')

@section('title', __('ui.profile_complete.page_title'))

@php
    $isEditing = request()->query('edit') === '1';
    $isCompleted = (bool) ($profile?->is_completed ?? false);
    $addressText = $profile?->address_text ?? ($profile?->address ?? '-');
    $hasLockedFullName = trim((string) ($profile?->full_name ?? '')) !== '';
    $hasLockedNik = preg_match('/^\d{16}$/', preg_replace('/[^0-9]/', '', (string) ($profile?->nik ?? ''))) === 1;
    $safeMapsUrl = trusted_map_embed_url((string) ($profile?->maps_url ?? ''), $addressText !== '-' ? $addressText : null);
@endphp

@section('content')
    <section class="bg-[#0A0A0B]">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#D4A843]">{{ __('Lengkapi Profil') }}</p>
                    <h1 class="mt-2 text-2xl font-semibold text-[#E8E8EC] sm:text-3xl">{{ __('Data Profil Penyewaan') }}</h1>
                    <p class="mt-2 text-sm text-[#A0A0A8]">{{ __('Lengkapi data profil, verifikasi email, dan verifikasi nomor telepon sebelum pembayaran.') }}</p>
                </div>
                @if ($isCompleted)
                    <span class="inline-flex items-center rounded-full bg-emerald-950/70 px-3 py-1 text-xs font-semibold text-emerald-300">
                        {{ __('ui.profile_complete.saved_badge') }}
                    </span>
                @endif
            </div>

            @if ($profilesTableMissing ?? false)
                <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    {{ __('Profil belum siap. Jalankan migrasi database terlebih dahulu.') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-5 rounded-md border border-emerald-500/20 bg-emerald-950/70 px-4 py-3 text-sm text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mt-5 rounded-md border border-emerald-500/20 bg-emerald-950/70 px-4 py-3 text-sm text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-5 rounded-md border border-rose-500/20 bg-rose-950/70 px-4 py-3 text-sm text-rose-300">
                    {{ session('error') }}
                </div>
            @endif

            @if ($isCompleted && ! $isEditing)
                @php
                    $genderLabel = match ($profile?->gender) {
                        'male' => __('Laki-laki'),
                        'female' => __('Perempuan'),
                        default => '-',
                    };
                @endphp
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1.35fr,0.9fr]">
                    <article class="mk-card rounded-lg p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                            <h2 class="text-lg font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Ringkasan Profil') }}</h2>
                            <p class="text-xs text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Data yang dipakai saat pembayaran.') }}</p>
                            </div>
                            <a href="{{ route('profile.complete', ['edit' => 1]) }}" class="inline-flex items-center rounded-md border border-[#1A1A1E] px-4 py-2 text-xs font-semibold text-[#A0A0A8] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                {{ __('Ubah Profil') }}
                            </a>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <section class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-4 dark:border-slate-800 dark:bg-slate-900/60">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Identitas') }}</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nama') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->full_name ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('NIK') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->masked_nik ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Tanggal lahir') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ optional($profile?->date_of_birth)->format('d M Y') ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Gender') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ $genderLabel }}</dd></div>
                                </dl>
                            </section>

                            <section class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-4 dark:border-slate-800 dark:bg-slate-900/60">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kontak Utama') }}</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Email') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ $user?->email ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('No. Telepon') }}</dt><dd class="text-right font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->phone ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Google Maps') }}</dt>
                                        <dd class="text-right font-semibold text-slate-800 dark:text-slate-100">
                                            @if (! empty($safeMapsUrl))
                                                <a href="{{ $safeMapsUrl }}" target="_blank" rel="noopener noreferrer" class="text-[#D4A843] hover:text-[#D4A843] dark:text-[#D4A843]">{{ __('Buka Link') }}</a>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-4 md:col-span-2 dark:border-slate-800 dark:bg-slate-900/60">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Alamat') }}</p>
                                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $addressText }}</p>
                            </section>

                            <section class="rounded-md border border-[#1A1A1E] bg-[#0A0A0B] p-4 md:col-span-2 dark:border-slate-800 dark:bg-slate-900/60">
                                <p class="text-xs font-semibold uppercase tracking-wider text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kontak Darurat') }}</p>
                                <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                                    <p><span class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nama') }}:</span> <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->emergency_name ?? '-' }}</span></p>
                                    <p><span class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Hubungan') }}:</span> <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->emergency_relation ?? '-' }}</span></p>
                                    <p><span class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('No. Telepon') }}:</span> <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $profile?->emergency_phone ?? '-' }}</span></p>
                                </div>
                            </section>
                        </div>
                    </article>

                    <aside class="space-y-4">
                        <div class="mk-card rounded-lg p-5">
                            <h3 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Status Verifikasi') }}</h3>
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between rounded-lg bg-[#0A0A0B] px-3 py-2 dark:bg-slate-900/60">
                                    <span class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Email') }}</span>
                                    <span class="font-semibold {{ $user?->hasVerifiedEmail() ? 'text-emerald-300' : 'text-amber-700' }}">{{ $user?->hasVerifiedEmail() ? __('Terverifikasi') : __('Belum') }}</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-[#0A0A0B] px-3 py-2 dark:bg-slate-900/60">
                                    <span class="text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nomor Telepon') }}</span>
                                    <span class="font-semibold {{ $profile?->phone_verified_at ? 'text-emerald-300' : 'text-amber-700' }}">{{ $profile?->phone_verified_at ? __('Terverifikasi') : __('Belum') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mk-card rounded-lg p-5">
                            <h3 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Langkah Aktivasi Pembayaran') }}</h3>
                            <ol class="mt-3 list-decimal space-y-2 pl-5 text-xs text-[#A0A0A8] dark:text-[#A0A0A8]">
                                <li>{{ __('Email harus terverifikasi.') }}</li>
                                <li>{{ __('Nomor telepon harus terverifikasi OTP.') }}</li>
                                <li>{{ __('Profil minimal harus lengkap.') }}</li>
                            </ol>
                        </div>

                        @if (! $profile?->phone_verified_at)
                            <a href="{{ route('phone.verify') }}" class="inline-flex w-full items-center justify-center rounded-md bg-[#D4A843] px-4 py-2.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                {{ __('Verifikasi Nomor Telepon') }}
                            </a>
                        @endif
                        <div class="grid grid-cols-1 gap-3">
                            <a href="{{ route('cart') }}" class="inline-flex w-full items-center justify-center rounded-md bg-[#D4A843] px-4 py-2.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                {{ __('Lanjut ke Keranjang') }}
                            </a>
                            <a href="{{ route('catalog') }}" class="inline-flex w-full items-center justify-center rounded-md border border-[#1A1A1E] bg-[#111113] px-4 py-2.5 text-sm font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                {{ __('Cari Alat Lain') }}
                            </a>
                        </div>
                    </aside>
                </div>
            @else
                <form method="POST" action="{{ route('profile.complete.store') }}" class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1.5fr,0.9fr]">
                    @csrf

                    <div class="space-y-6">
                        <article class="mk-card rounded-lg p-6">
                            <h2 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Identitas') }}</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nama Lengkap') }}</label>
                                    <input type="text" name="full_name" value="{{ old('full_name', $profile->full_name ?? $user?->name) }}" @if ($hasLockedFullName) readonly @endif required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @if($hasLockedFullName) bg-[#1A1A1E] text-[#66666C] cursor-not-allowed border-none @endif @error('full_name') border-rose-400 @enderror">
                                    @if ($hasLockedFullName)
                                        <p class="mt-1 text-xs text-[#A0A0A8]">{{ __('Nama sudah dikunci untuk keamanan data identitas.') }}</p>
                                    @endif
                                    @error('full_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('NIK (16 digit)') }}</label>
                                    <input type="text" name="nik" value="{{ old('nik', $profile->nik ?? $profile->identity_number) }}" @if ($hasLockedNik) readonly @endif required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @if($hasLockedNik) bg-[#1A1A1E] text-[#66666C] cursor-not-allowed border-none @endif @error('nik') border-rose-400 @enderror">
                                    @if ($hasLockedNik)
                                        <p class="mt-1 text-xs text-[#A0A0A8]">{{ __('NIK sudah dikunci dan tidak dapat diubah.') }}</p>
                                    @endif
                                    @error('nik')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Tanggal Lahir') }}</label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('date_of_birth') border-rose-400 @enderror">
                                    @error('date_of_birth')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Gender') }}</label>
                                    <select name="gender" class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('gender') border-rose-400 @enderror">
                                        <option value="" disabled @selected(! in_array(old('gender', $profile->gender ?? ''), ['male', 'female'], true))>{{ __('Pilih') }}</option>
                                        <option value="male" @selected(old('gender', $profile->gender ?? '') === 'male')>{{ __('Laki-laki') }}</option>
                                        <option value="female" @selected(old('gender', $profile->gender ?? '') === 'female')>{{ __('Perempuan') }}</option>
                                    </select>
                                    @error('gender')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <article class="mk-card rounded-lg p-6">
                            <h2 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Kontak & Alamat') }}</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nomor Telepon') }}</label>
                                    <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('phone') border-rose-400 @enderror">
                                    @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Google Maps Link (opsional)') }}</label>
                                    <input type="url" name="maps_url" value="{{ old('maps_url', $profile->maps_url ?? '') }}" class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('maps_url') border-rose-400 @enderror">
                                    @error('maps_url')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Jalan, Nomor, RT/RW') }}</label>
                                    <input type="text" name="address_line" value="{{ old('address_line', $profile->address_line ?? $profile->address ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('address_line') border-rose-400 @enderror">
                                    @error('address_line')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kelurahan') }}</label>
                                    <input type="text" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('kelurahan') border-rose-400 @enderror">
                                    @error('kelurahan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kecamatan') }}</label>
                                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $profile->kecamatan ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('kecamatan') border-rose-400 @enderror">
                                    @error('kecamatan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kota / Kabupaten') }}</label>
                                    <input type="text" name="city" value="{{ old('city', $profile->city ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('city') border-rose-400 @enderror">
                                    @error('city')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Provinsi') }}</label>
                                    <input type="text" name="province" value="{{ old('province', $profile->province ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('province') border-rose-400 @enderror">
                                    @error('province')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Kode Pos') }}</label>
                                    <input type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('postal_code') border-rose-400 @enderror">
                                    @error('postal_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <article class="mk-card rounded-lg p-6">
                            <h2 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Kontak Darurat') }}</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-3">
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Nama') }}</label>
                                    <input type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? $profile->emergency_contact ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('emergency_name') border-rose-400 @enderror">
                                    @error('emergency_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('Hubungan') }}</label>
                                    <input type="text" name="emergency_relation" value="{{ old('emergency_relation', $profile->emergency_relation ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('emergency_relation') border-rose-400 @enderror">
                                    @error('emergency_relation')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#A0A0A8] dark:text-[#A0A0A8]">{{ __('No. Telepon') }}</label>
                                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" required class="input mt-2 w-full rounded-xl border border-[#1A1A1E] bg-[#0A0A0B]/50 px-4 py-3 text-sm text-white placeholder-[#66666C] focus:border-[#D4A843] focus:bg-[#0A0A0B] focus:ring-1 focus:ring-[#D4A843] transition-all @error('emergency_phone') border-rose-400 @enderror">
                                    @error('emergency_phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#D4A843] px-6 py-3 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                {{ __('Simpan Profil') }}
                            </button>
                            <a href="{{ route('overview') }}" class="inline-flex items-center justify-center rounded-md border border-[#1A1A1E]  bg-[#111113] px-6 py-3 text-sm font-semibold text-[#A0A0A8] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                {{ __('Nanti Saja') }}
                            </a>
                        </div>
                    </div>

                    <aside class="space-y-4">
                        <div class="mk-card rounded-lg p-5">
                            <h3 class="text-sm font-semibold text-[#E8E8EC] dark:text-slate-50">{{ __('Syarat Pembayaran') }}</h3>
                            <ul class="mt-3 list-disc space-y-2 pl-5 text-xs text-[#A0A0A8] dark:text-[#A0A0A8]">
                                <li>{{ __('Profil lengkap dan valid.') }}</li>
                                <li>{{ __('Email sudah terverifikasi.') }}</li>
                                <li>{{ __('Nomor telepon sudah terverifikasi OTP.') }}</li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-[#1A1A1E] bg-[#111113] p-5 text-xs text-[#D4A843] shadow-sm">
                            {{ __('Setelah simpan profil, kamu akan diarahkan ke verifikasi nomor telepon bila belum terverifikasi.') }}
                        </div>
                    </aside>
                </form>
            @endif
        </div>
    </section>
@endsection

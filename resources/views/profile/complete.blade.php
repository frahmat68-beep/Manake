@extends('layouts.app')

@section('title', __('ui.profile_complete.page_title'))

@php
    $isEditing = request()->query('edit') === '1';
    $isCompleted = (bool) ($profile?->is_completed ?? false);
    $addressText = $profile?->address_text ?? ($profile?->address ?? '-');
@endphp

@section('content')
    <section class="bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-600">Lengkapi Profil</p>
                    <h1 class="mt-2 text-2xl font-semibold text-slate-900 sm:text-3xl">Data Profil Penyewaan</h1>
                    <p class="mt-2 text-sm text-slate-600">Lengkapi data profil, verifikasi email, dan verifikasi nomor telepon sebelum pembayaran.</p>
                </div>
                @if ($isCompleted)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        {{ __('ui.profile_complete.saved_badge') }}
                    </span>
                @endif
            </div>

            @if ($profilesTableMissing ?? false)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Profil belum siap. Jalankan migrasi database terlebih dahulu.
                </div>
            @endif

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($isCompleted && ! $isEditing)
                @php
                    $genderLabel = match ($profile?->gender) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        'other' => 'Lainnya',
                        default => '-',
                    };
                @endphp
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1.35fr,0.9fr]">
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Profil</h2>
                                <p class="text-xs text-slate-500">Data yang dipakai saat pembayaran.</p>
                            </div>
                            <a href="{{ route('profile.complete', ['edit' => 1]) }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                Ubah Profil
                            </a>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <section class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Identitas</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Nama</dt><dd class="text-right font-semibold text-slate-800">{{ $profile?->full_name ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">NIK</dt><dd class="text-right font-semibold text-slate-800">{{ $profile?->masked_nik ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Tanggal lahir</dt><dd class="text-right font-semibold text-slate-800">{{ optional($profile?->date_of_birth)->format('d M Y') ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Gender</dt><dd class="text-right font-semibold text-slate-800">{{ $genderLabel }}</dd></div>
                                </dl>
                            </section>

                            <section class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kontak Utama</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="text-right font-semibold text-slate-800">{{ $user?->email ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">No. Telepon</dt><dd class="text-right font-semibold text-slate-800">{{ $profile?->phone ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Google Maps</dt>
                                        <dd class="text-right font-semibold text-slate-800">
                                            @if (! empty($profile?->maps_url))
                                                <a href="{{ $profile->maps_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-700">Buka Link</a>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="rounded-xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat</p>
                                <p class="mt-3 text-sm font-semibold text-slate-800">{{ $addressText }}</p>
                            </section>

                            <section class="rounded-xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kontak Darurat</p>
                                <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                                    <p><span class="text-slate-500">Nama:</span> <span class="font-semibold text-slate-800">{{ $profile?->emergency_name ?? '-' }}</span></p>
                                    <p><span class="text-slate-500">Hubungan:</span> <span class="font-semibold text-slate-800">{{ $profile?->emergency_relation ?? '-' }}</span></p>
                                    <p><span class="text-slate-500">No. Telepon:</span> <span class="font-semibold text-slate-800">{{ $profile?->emergency_phone ?? '-' }}</span></p>
                                </div>
                            </section>
                        </div>
                    </article>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Status Verifikasi</h3>
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="text-slate-600">Email</span>
                                    <span class="font-semibold {{ $user?->hasVerifiedEmail() ? 'text-emerald-700' : 'text-amber-700' }}">{{ $user?->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum' }}</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="text-slate-600">Nomor Telepon</span>
                                    <span class="font-semibold {{ $profile?->phone_verified_at ? 'text-emerald-700' : 'text-amber-700' }}">{{ $profile?->phone_verified_at ? 'Terverifikasi' : 'Belum' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Langkah Aktivasi Pembayaran</h3>
                            <ol class="mt-3 list-decimal space-y-2 pl-5 text-xs text-slate-600">
                                <li>Email harus terverifikasi.</li>
                                <li>Nomor telepon harus terverifikasi OTP.</li>
                                <li>Profil minimal harus lengkap.</li>
                            </ol>
                        </div>

                        @if (! $profile?->phone_verified_at)
                            <a href="{{ route('phone.verify') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Verifikasi Nomor Telepon
                            </a>
                        @endif
                    </aside>
                </div>
            @else
                <form method="POST" action="{{ route('profile.complete.store') }}" class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1.5fr,0.9fr]">
                    @csrf

                    <div class="space-y-6">
                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-sm font-semibold text-slate-900">Identitas</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Nama Lengkap</label>
                                    <input type="text" name="full_name" value="{{ old('full_name', $profile->full_name ?? $user?->name) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('full_name') border-rose-400 @enderror">
                                    @error('full_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">NIK (16 digit)</label>
                                    <input type="text" name="nik" value="{{ old('nik', $profile->nik ?? $profile->identity_number) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('nik') border-rose-400 @enderror">
                                    @error('nik')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Tanggal Lahir</label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('date_of_birth') border-rose-400 @enderror">
                                    @error('date_of_birth')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Gender (opsional)</label>
                                    <select name="gender" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('gender') border-rose-400 @enderror">
                                        <option value="">Pilih</option>
                                        <option value="male" @selected(old('gender', $profile->gender ?? '') === 'male')>Laki-laki</option>
                                        <option value="female" @selected(old('gender', $profile->gender ?? '') === 'female')>Perempuan</option>
                                        <option value="other" @selected(old('gender', $profile->gender ?? '') === 'other')>Lainnya</option>
                                    </select>
                                    @error('gender')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-sm font-semibold text-slate-900">Kontak & Alamat</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Nomor Telepon</label>
                                    <input type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('phone') border-rose-400 @enderror">
                                    @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Google Maps Link (opsional)</label>
                                    <input type="url" name="maps_url" value="{{ old('maps_url', $profile->maps_url ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('maps_url') border-rose-400 @enderror">
                                    @error('maps_url')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs font-semibold text-slate-500">Jalan, Nomor, RT/RW</label>
                                    <input type="text" name="address_line" value="{{ old('address_line', $profile->address_line ?? $profile->address ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('address_line') border-rose-400 @enderror">
                                    @error('address_line')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Kelurahan</label>
                                    <input type="text" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('kelurahan') border-rose-400 @enderror">
                                    @error('kelurahan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Kecamatan</label>
                                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $profile->kecamatan ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('kecamatan') border-rose-400 @enderror">
                                    @error('kecamatan')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Kota / Kabupaten</label>
                                    <input type="text" name="city" value="{{ old('city', $profile->city ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('city') border-rose-400 @enderror">
                                    @error('city')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Provinsi</label>
                                    <input type="text" name="province" value="{{ old('province', $profile->province ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('province') border-rose-400 @enderror">
                                    @error('province')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Kode Pos</label>
                                    <input type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('postal_code') border-rose-400 @enderror">
                                    @error('postal_code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-sm font-semibold text-slate-900">Kontak Darurat</h2>
                            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Nama</label>
                                    <input type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? $profile->emergency_contact ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('emergency_name') border-rose-400 @enderror">
                                    @error('emergency_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">Hubungan</label>
                                    <input type="text" name="emergency_relation" value="{{ old('emergency_relation', $profile->emergency_relation ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('emergency_relation') border-rose-400 @enderror">
                                    @error('emergency_relation')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500">No. Telepon</label>
                                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none @error('emergency_phone') border-rose-400 @enderror">
                                    @error('emergency_phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </article>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Simpan Profil
                            </button>
                            <a href="{{ route('overview') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                Nanti Saja
                            </a>
                        </div>
                    </div>

                    <aside class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Syarat Pembayaran</h3>
                            <ul class="mt-3 list-disc space-y-2 pl-5 text-xs text-slate-600">
                                <li>Profil lengkap dan valid.</li>
                                <li>Email sudah terverifikasi.</li>
                                <li>Nomor telepon sudah terverifikasi OTP.</li>
                            </ul>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-xs text-blue-700 shadow-sm">
                            Setelah simpan profil, kamu akan diarahkan ke verifikasi nomor telepon bila belum terverifikasi.
                        </div>
                    </aside>
                </form>
            @endif
        </div>
    </section>
@endsection

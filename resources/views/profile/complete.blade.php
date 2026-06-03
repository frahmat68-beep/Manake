@extends('layouts.app')

@section('title', __('ui.profile_complete.page_title'))

@php
    $profileCopy = __('ui.profile_complete');
    $safeMapsUrl = trusted_map_embed_url((string) ($profile?->maps_url ?? ''), $profile?->address_text ?? null);
    $profileComplete = (bool) ($user?->hasCompleteRentalProfile() ?? false);
    $emailVerified = (bool) ($user?->hasVerifiedEmail() ?? false);
    $consentAccepted = (bool) optional($profile)->rental_consent_accepted_at;
    $allReady = $profileComplete && $emailVerified && $consentAccepted;
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

    $profileStatus = [
        'done' => $profileComplete,
        'label' => $profileComplete ? 'Selesai' : 'Belum',
        'name' => 'Profil Lengkap',
        'tone' => $profileComplete ? 'profile-status-done' : 'profile-status-pending',
    ];

    $emailStatus = [
        'done' => $emailVerified,
        'label' => $emailVerified ? 'Terverifikasi' : 'Belum',
        'name' => 'Email Terverifikasi',
        'tone' => $emailVerified ? 'profile-status-done' : 'profile-status-pending',
    ];

    $consentStatus = [
        'done' => $consentAccepted,
        'label' => $consentAccepted ? 'Disetujui' : 'Belum',
        'name' => 'Persetujuan Tanggung Jawab',
        'tone' => $consentAccepted ? 'profile-status-done' : 'profile-status-pending',
    ];
@endphp

@push('head')
    <style>
        .profile-page {
            --profile-accent: #D4A843;
            --profile-accent-hover: #E0BA5D;
            --profile-accent-text: #0A0A0B;
            --profile-accent-soft: rgba(212, 168, 67, 0.12);
            --profile-accent-border: rgba(212, 168, 67, 0.28);

            --profile-bg: #0A0A0B;
            --profile-surface: #111113;
            --profile-surface-soft: rgba(17, 17, 19, 0.72);
            --profile-surface-muted: #0A0A0B;
            --profile-border: #1A1A1E;
            --profile-text: #E8E8EC;
            --profile-muted: #A0A0A8;
            --profile-subtle: #7C7C84;
        }

        html[data-theme-resolved="light"] .profile-page {
            --profile-accent: #2563EB;
            --profile-accent-hover: #1D4ED8;
            --profile-accent-text: #FFFFFF;
            --profile-accent-soft: rgba(37, 99, 235, 0.10);
            --profile-accent-border: rgba(37, 99, 235, 0.24);

            --profile-bg: #F8FAFC;
            --profile-surface: #FFFFFF;
            --profile-surface-soft: rgba(255, 255, 255, 0.94);
            --profile-surface-muted: #F8FAFC;
            --profile-border: #E5E7EB;
            --profile-text: #111827;
            --profile-muted: #4B5563;
            --profile-subtle: #6B7280;
        }

        .profile-page-bg {
            background-color: var(--profile-bg) !important;
            color: var(--profile-text) !important;
        }

        .profile-card {
            background: var(--profile-surface-soft) !important;
            border-color: var(--profile-border) !important;
            color: var(--profile-text) !important;
        }

        .profile-card-solid {
            background: var(--profile-surface) !important;
            border-color: var(--profile-border) !important;
            color: var(--profile-text) !important;
        }

        .profile-inner {
            background: var(--profile-surface-muted) !important;
            border-color: var(--profile-border) !important;
            color: var(--profile-text) !important;
        }

        .profile-title {
            color: var(--profile-text) !important;
        }

        .profile-muted {
            color: var(--profile-muted) !important;
        }

        .profile-subtle {
            color: var(--profile-subtle) !important;
        }

        .profile-accent-text {
            color: var(--profile-accent) !important;
        }

        .profile-accent-bg {
            background: var(--profile-accent) !important;
            background-color: var(--profile-accent) !important;
            color: var(--profile-accent-text) !important;
            border-color: var(--profile-accent) !important;
        }

        .profile-accent-bg:hover {
            background: var(--profile-accent-hover) !important;
            background-color: var(--profile-accent-hover) !important;
        }

        .profile-accent-soft {
            background: var(--profile-accent-soft) !important;
            border-color: var(--profile-accent-border) !important;
            color: var(--profile-accent) !important;
        }

        .profile-secondary-button {
            background: var(--profile-surface) !important;
            border: 1px solid var(--profile-border) !important;
            color: var(--profile-text) !important;
        }

        .profile-secondary-button:hover {
            border-color: var(--profile-accent-border) !important;
            color: var(--profile-accent) !important;
        }

        .profile-input {
            background: var(--profile-surface) !important;
            border: 1px solid var(--profile-border) !important;
            color: var(--profile-text) !important;
            border-radius: 0.875rem !important;
            outline: none !important;
        }

        .profile-input:focus {
            border-color: var(--profile-accent) !important;
            box-shadow: 0 0 0 3px var(--profile-accent-soft) !important;
        }

        .profile-input[readonly] {
            background: var(--profile-surface-muted) !important;
            color: var(--profile-muted) !important;
            cursor: not-allowed;
        }

        html[data-theme-resolved="light"] .profile-input {
            color-scheme: light !important;
        }

        html[data-theme-resolved="dark"] .profile-input {
            color-scheme: dark !important;
        }

        .profile-required {
            color: var(--profile-accent) !important;
        }

        .profile-status-done {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .profile-status-pending {
            border-color: rgba(245, 158, 11, 0.28) !important;
            background: #FFFBEB !important;
            color: #B45309 !important;
        }

        html[data-theme-resolved="dark"] .profile-status-done {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .profile-status-pending {
            background: rgba(245, 158, 11, 0.12) !important;
            color: #FDE68A !important;
        }

        .profile-alert-success {
            border-color: rgba(16, 185, 129, 0.28) !important;
            background: #ECFDF5 !important;
            color: #047857 !important;
        }

        .profile-alert-warning {
            border-color: rgba(245, 158, 11, 0.28) !important;
            background: #FFFBEB !important;
            color: #B45309 !important;
        }

        .profile-alert-error {
            border-color: rgba(244, 63, 94, 0.28) !important;
            background: #FFF1F2 !important;
            color: #BE123C !important;
        }

        html[data-theme-resolved="dark"] .profile-alert-success {
            background: rgba(6, 78, 59, 0.38) !important;
            color: #A7F3D0 !important;
        }

        html[data-theme-resolved="dark"] .profile-alert-warning {
            background: rgba(120, 53, 15, 0.38) !important;
            color: #FDE68A !important;
        }

        html[data-theme-resolved="dark"] .profile-alert-error {
            background: rgba(136, 19, 55, 0.38) !important;
            color: #FDA4AF !important;
        }

        html[data-theme-resolved="light"] .profile-page .profile-card,
        html[data-theme-resolved="light"] .profile-page .profile-card-solid {
            box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.22);
        }

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
    <section class="profile-page profile-page-bg min-h-screen">
        <div class="mx-auto w-full max-w-[1280px] px-4 py-8 pb-24 sm:px-6 lg:px-8 lg:py-10">
            <header class="profile-card profile-shell-enter relative overflow-hidden rounded-3xl border p-6 shadow-[0_24px_70px_-50px_rgba(0,0,0,0.35)] sm:p-7">
                <span class="profile-accent-bg absolute left-0 top-0 h-1 w-full"></span>
                <div class="space-y-5">
                    <div class="space-y-2">
                        <p class="profile-accent-text text-xs font-semibold tracking-[0.18em] uppercase">{{ $profileCopy['kicker'] }}</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h1 class="text-2xl font-bold tracking-tight profile-title sm:text-3xl">{{ $profileCopy['title'] }}</h1>
                            @if ($hasSavedProfile)
                                <span class="profile-status-done inline-flex w-fit items-center gap-2 rounded-2xl border px-3 py-2 text-sm font-medium">
                                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                    {{ $profileCopy['saved_badge'] }}
                                </span>
                            @endif
                        </div>
                        <p class="max-w-3xl text-sm leading-6 profile-muted sm:text-base">
                            {{ $profileCopy['subtitle'] }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        @foreach ([$profileStatus, $emailStatus, $phoneStatus] as $chip)
                            <div class="inline-flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $chip['tone'] }}">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-current/15">
                                    @if ($chip['done'])
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
                                    <p class="text-sm font-semibold">{{ $chip['name'] }}</p>
                                    <p class="text-xs opacity-85">{{ $chip['label'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </header>

            @if ($profilesTableMissing ?? false)
                <div class="mt-5 profile-alert-warning rounded-2xl border px-4 py-3 text-sm">
                    {{ $profileCopy['migration_hint'] }}
                </div>
            @endif

            @if ($statusMessage || $successMessage || $warningMessage || $errorMessage || $errors->any())
                <div class="mt-5 space-y-3">
                    @if ($statusMessage)
                        <div class="profile-alert-success rounded-2xl border px-4 py-3 text-sm">
                            {{ is_string($statusMessage) ? __($statusMessage) : $statusMessage }}
                        </div>
                    @endif
                    @if ($successMessage)
                        <div class="profile-alert-success rounded-2xl border px-4 py-3 text-sm">
                            {{ $successMessage }}
                        </div>
                    @endif
                    @if ($warningMessage)
                        <div class="profile-alert-warning rounded-2xl border px-4 py-3 text-sm">
                            {{ $warningMessage }}
                        </div>
                    @endif
                    @if ($errorMessage)
                        <div class="profile-alert-error rounded-2xl border px-4 py-3 text-sm">
                            {{ $errorMessage }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="profile-alert-error rounded-2xl border px-4 py-3 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                <form method="POST" action="{{ route('profile.store') }}" class="space-y-6">
                    @csrf

                    <article class="profile-card profile-card-in rounded-3xl border p-6 shadow-2xl sm:p-7">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight profile-title">{{ $profileCopy['identity_title'] }}</h2>
                            <p class="text-sm profile-muted">{{ $profileCopy['identity_subtitle'] }} <span class="text-amber-500 font-semibold block mt-1">Nama dan NIK dikunci setelah tersimpan untuk keamanan penyewaan.</span></p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="full_name" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['full_name'] }} <span class="profile-required">*</span></label>
                                <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $profile->full_name ?? $user?->name) }}" @if ($hasLockedFullName) readonly @endif required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('full_name') border-rose-400 @enderror">
                                @error('full_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="nik" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['national_id'] }} <span class="profile-required">*</span></label>
                                <input id="nik" type="text" name="nik" value="{{ old('nik', $profile->nik ?? $profile->identity_number) }}" @if ($hasLockedNik) readonly @endif required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('nik') border-rose-400 @enderror">
                                <p class="mt-1 text-xs profile-muted">{{ $profileCopy['helpers']['national_id'] }}</p>
                                @error('nik')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="date_of_birth" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['date_of_birth'] }} <span class="profile-required">*</span></label>
                                <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('date_of_birth') border-rose-400 @enderror">
                                @error('date_of_birth')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="gender" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['gender'] }} <span class="profile-required">*</span></label>
                                <select id="gender" name="gender" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('gender') border-rose-400 @enderror">
                                    <option value="" disabled @selected(! in_array(old('gender', $profile->gender ?? ''), ['male', 'female'], true))>{{ $profileCopy['gender_placeholder'] }}</option>
                                    <option value="male" @selected(old('gender', $profile->gender ?? '') === 'male')>{{ $profileCopy['gender_male'] }}</option>
                                    <option value="female" @selected(old('gender', $profile->gender ?? '') === 'female')>{{ $profileCopy['gender_female'] }}</option>
                                </select>
                                @error('gender')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <article class="profile-card profile-card-in rounded-3xl border p-6 shadow-2xl sm:p-7 animate-delay-[70ms]">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight profile-title">{{ $profileCopy['contact_title'] }}</h2>
                            <p class="text-sm profile-muted">{{ $profileCopy['contact_subtitle'] }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="phone" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['phone'] }} <span class="profile-required">*</span></label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('phone') border-rose-400 @enderror">
                                <p class="mt-1 text-xs profile-muted">{{ $profileCopy['helpers']['phone'] }}</p>
                                @error('phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="maps_url" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['maps_url'] }}</label>
                                <input id="maps_url" type="url" name="maps_url" value="{{ old('maps_url', $profile->maps_url ?? '') }}" class="profile-input mt-2 w-full px-4 py-3 text-sm @error('maps_url') border-rose-400 @enderror">
                                @error('maps_url')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="alternative_phone" class="text-sm font-medium profile-title">Nomor Telepon Alternatif (Opsional)</label>
                                <input id="alternative_phone" type="text" name="alternative_phone" value="{{ old('alternative_phone', $profile->alternative_phone ?? '') }}" placeholder="08xxxxxxxxxx" class="profile-input mt-2 w-full px-4 py-3 text-sm @error('alternative_phone') border-rose-400 @enderror">
                                @error('alternative_phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="instagram_handle" class="text-sm font-medium profile-title">Username Instagram (Opsional)</label>
                                <input id="instagram_handle" type="text" name="instagram_handle" value="{{ old('instagram_handle', $profile->instagram_handle ?? '') }}" placeholder="@username" class="profile-input mt-2 w-full px-4 py-3 text-sm @error('instagram_handle') border-rose-400 @enderror">
                                @error('instagram_handle')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="organization_name" class="text-sm font-medium profile-title">Nama Instansi / Organisasi (Opsional)</label>
                                <input id="organization_name" type="text" name="organization_name" value="{{ old('organization_name', $profile->organization_name ?? '') }}" class="profile-input mt-2 w-full px-4 py-3 text-sm @error('organization_name') border-rose-400 @enderror">
                                @error('organization_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="organization_type" class="text-sm font-medium profile-title">Jenis Instansi (Opsional)</label>
                                <select id="organization_type" name="organization_type" class="profile-input mt-2 w-full px-4 py-3 text-sm @error('organization_type') border-rose-400 @enderror">
                                    <option value="" @selected(empty(old('organization_type', $profile->organization_type ?? '')))>-- Pilih Jenis Instansi --</option>
                                    <option value="student" @selected(old('organization_type', $profile->organization_type ?? '') === 'student')>Kemahasiswaan / Sekolah</option>
                                    <option value="production_house" @selected(old('organization_type', $profile->organization_type ?? '') === 'production_house')>Production House (PH)</option>
                                    <option value="freelance" @selected(old('organization_type', $profile->organization_type ?? '') === 'freelance')>Pekerja Lepas / Freelance</option>
                                    <option value="event_organizer" @selected(old('organization_type', $profile->organization_type ?? '') === 'event_organizer')>Event Organizer (EO)</option>
                                    <option value="general" @selected(old('organization_type', $profile->organization_type ?? '') === 'general')>Lainnya / Umum</option>
                                </select>
                                @error('organization_type')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address_line" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['address_line'] }} <span class="profile-required">*</span></label>
                                <input id="address_line" type="text" name="address_line" value="{{ old('address_line', $profile->address_line ?? $profile->address ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('address_line') border-rose-400 @enderror">
                                <p class="mt-1 text-xs profile-muted">{{ $profileCopy['helpers']['address_line'] }}</p>
                                @error('address_line')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="kelurahan" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['kelurahan'] }} <span class="profile-required">*</span></label>
                                <input id="kelurahan" type="text" name="kelurahan" value="{{ old('kelurahan', $profile->kelurahan ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('kelurahan') border-rose-400 @enderror">
                                @error('kelurahan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="kecamatan" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['kecamatan'] }} <span class="profile-required">*</span></label>
                                <input id="kecamatan" type="text" name="kecamatan" value="{{ old('kecamatan', $profile->kecamatan ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('kecamatan') border-rose-400 @enderror">
                                @error('kecamatan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="city" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['city'] }} <span class="profile-required">*</span></label>
                                <input id="city" type="text" name="city" value="{{ old('city', $profile->city ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('city') border-rose-400 @enderror">
                                @error('city')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="province" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['province'] }} <span class="profile-required">*</span></label>
                                <input id="province" type="text" name="province" value="{{ old('province', $profile->province ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('province') border-rose-400 @enderror">
                                @error('province')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="postal_code" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['postal_code'] }} <span class="profile-required">*</span></label>
                                <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('postal_code') border-rose-400 @enderror">
                                @error('postal_code')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <article class="profile-card profile-card-in rounded-3xl border p-6 shadow-2xl sm:p-7 animate-delay-[140ms]">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight profile-title">{{ $profileCopy['emergency_title'] }}</h2>
                            <p class="text-sm profile-muted">{{ $profileCopy['emergency_subtitle'] }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div>
                                <label for="emergency_name" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['emergency_name'] }} <span class="profile-required">*</span></label>
                                <input id="emergency_name" type="text" name="emergency_name" value="{{ old('emergency_name', $profile->emergency_name ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('emergency_name') border-rose-400 @enderror">
                                @error('emergency_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="emergency_relation" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['emergency_relation'] }} <span class="profile-required">*</span></label>
                                <input id="emergency_relation" type="text" name="emergency_relation" value="{{ old('emergency_relation', $profile->emergency_relation ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('emergency_relation') border-rose-400 @enderror">
                                @error('emergency_relation')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="emergency_phone" class="text-sm font-medium profile-title">{{ $profileCopy['labels']['emergency_phone'] }} <span class="profile-required">*</span></label>
                                <input id="emergency_phone" type="text" name="emergency_phone" value="{{ old('emergency_phone', $profile->emergency_phone ?? '') }}" required class="profile-input mt-2 w-full px-4 py-3 text-sm @error('emergency_phone') border-rose-400 @enderror">
                                @error('emergency_phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </article>

                    <div class="profile-card-in space-y-3 animate-delay-[180ms] rounded-2xl border p-4 mb-4" style="border-color: var(--profile-border); background: var(--profile-surface);">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="rental_responsibility_consent" value="1" required @checked(old('rental_responsibility_consent') || ($profile?->rental_consent_accepted_at ?? false)) class="mt-1 rounded border-gray-300 text-[var(--profile-accent)] focus:ring-[var(--profile-accent)]">
                            <span class="text-xs sm:text-sm leading-relaxed profile-muted">
                                Saya menyatakan data yang saya isi benar dan dapat digunakan Manake untuk verifikasi penyewaan, pengambilan alat, pengembalian alat, serta penanganan keterlambatan, kerusakan, atau kehilangan barang.
                            </span>
                        </label>
                        @error('rental_responsibility_consent')
                            <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profile-card-in flex flex-col gap-3 sm:flex-row animate-delay-[210ms]">
                        <button type="submit" class="profile-accent-bg inline-flex items-center justify-center rounded-xl px-6 py-3.5 text-sm font-semibold transition focus:outline-none">
                            {{ $profileCopy['save_profile'] }}
                        </button>
                        <a href="{{ route('catalog') }}" class="profile-secondary-button inline-flex items-center justify-center rounded-xl px-6 py-3.5 text-sm font-semibold transition">
                            {{ $profileCopy['back_to_catalog'] }}
                        </a>
                    </div>
                </form>

                <aside class="space-y-6 lg:sticky lg:top-28">
                    <article class="profile-card profile-card-in rounded-3xl border p-6 shadow-2xl animate-delay-[60ms]">
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold tracking-tight profile-title">{{ $profileCopy['sidebar_title'] }}</h2>
                            <p class="text-sm profile-muted">{{ $profileCopy['sidebar_subtitle'] }}</p>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ([$profileStatus, $emailStatus, $consentStatus] as $item)
                                <div class="profile-inner flex items-center justify-between gap-4 rounded-2xl border px-4 py-3">
                                    <span class="text-sm font-medium profile-title">{{ $item['name'] }}</span>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $item['tone'] }}">
                                        {{ $item['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Status message & helpers -->
                        <div class="profile-inner mt-5 rounded-2xl border p-4">
                            @if (! $profileComplete)
                                <p class="text-sm leading-6 profile-muted">Lengkapi dan simpan data profil terlebih dahulu.</p>
                            @elseif (! $emailVerified)
                                <p class="text-sm leading-6 profile-muted">Verifikasi email terlebih dahulu untuk dapat memesan alat.</p>
                            @elseif (! $consentAccepted)
                                <p class="text-sm leading-6 profile-muted">Simpan profil untuk menyetujui pernyataan tanggung jawab sewa.</p>
                            @else
                                <p class="text-sm leading-6 text-emerald-700 dark:text-emerald-200">Profil siap digunakan untuk pemesanan.</p>
                            @endif
                        </div>

                        <!-- Action Buttons and Forms -->
                        <div class="mt-4 space-y-4">
                            <!-- Email verification -->
                            <div class="profile-inner rounded-2xl border p-4 space-y-3">
                                <p class="text-sm font-semibold profile-title">Verifikasi Email</p>
                                @if (! $emailVerified)
                                    <form method="POST" action="{{ route('verification.send') }}" class="space-y-2">
                                        @csrf
                                        <button type="submit" class="profile-accent-bg inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                            Verifikasi Email
                                        </button>
                                        <button type="submit" class="profile-secondary-button inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-xs font-medium transition">
                                            Kirim Ulang Link Verifikasi
                                        </button>
                                    </form>
                                @else
                                    <button disabled class="profile-secondary-button opacity-60 inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold cursor-not-allowed">
                                        Email Terverifikasi
                                    </button>
                                @endif
                            </div>

                            <!-- Nomor Telepon Penyewa -->
                            <div class="profile-inner rounded-2xl border p-4 space-y-3">
                                <p class="text-sm font-semibold profile-title">Nomor Telepon Penyewa</p>
                                <p class="text-xs profile-muted leading-5">Nomor telepon wajib diisi sebagai kontak utama penyewa. Admin akan mencocokkan nomor telepon dan identitas fisik saat pengambilan alat.</p>
                                @if ($profile?->phone)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold profile-status-done">Nomor Tersimpan</span>
                                        <span class="text-xs font-mono profile-muted">{{ $profile->phone }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold profile-status-pending">Belum Diisi</span>
                                @endif
                            </div>

                            <!-- Lanjutkan / Continue CTA -->
                            <div class="mt-6 pt-4 border-t border-[var(--profile-border)]">
                                @if ($allReady)
                                    <a href="{{ route('profile.continue') }}" class="profile-accent-bg inline-flex w-full items-center justify-center rounded-xl px-4 py-3.5 text-sm font-semibold transition shadow-md">
                                        @if (session('after_profile_redirect'))
                                            Lanjutkan Proses Sewa
                                        @else
                                            Buka Katalog
                                        @endif
                                    </a>
                                @else
                                    <div class="space-y-2">
                                        <p class="text-xs text-rose-400 font-medium">Lengkapi profil dan verifikasi akun sebelum melanjutkan pemesanan.</p>
                                        <button disabled class="profile-secondary-button opacity-50 inline-flex w-full items-center justify-center rounded-xl px-4 py-3.5 text-sm font-semibold cursor-not-allowed">
                                            @if (session('after_profile_redirect'))
                                                Lanjutkan Proses Sewa
                                            @else
                                                Buka Katalog
                                            @endif
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>

                    <article class="profile-card profile-card-in rounded-3xl border p-6 shadow-2xl animate-delay-[120ms]">
                        <h3 class="text-base font-semibold profile-title">{{ $profileCopy['quick_help_title'] }}</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="profile-muted">{{ $profileCopy['quick_help_email'] }}</dt>
                                <dd class="text-right font-medium profile-title">{{ $user?->email ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="profile-muted">{{ $profileCopy['quick_help_phone'] }}</dt>
                                <dd class="text-right font-medium profile-title">{{ $profile?->phone ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="profile-muted">{{ $profileCopy['quick_help_location'] }}</dt>
                                <dd class="text-right font-medium profile-title">
                                    @if ($safeMapsUrl)
                                        <a href="{{ $safeMapsUrl }}" target="_blank" rel="noopener noreferrer" class="profile-accent-text hover:underline">
                                            {{ $profileCopy['quick_help_location_cta'] }}
                                        </a>
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

@extends('layouts.app')

@section('title', 'Verifikasi Email | Manake')

@push('head')
<style>
.email-verify-page {
    --verify-bg: #0A0A0B;
    --verify-surface: #111113;
    --verify-surface-soft: #0A0A0B;
    --verify-border: #1A1A1E;
    --verify-text: #E8E8EC;
    --verify-muted: #A0A0A8;
    --verify-accent: #D4A843;
    --verify-accent-hover: #E0BA5D;
    --verify-accent-text: #0A0A0B;
    background-color: var(--verify-bg) !important;
}
html[data-theme-resolved="light"] .email-verify-page {
    --verify-bg: #F8FAFC;
    --verify-surface: #FFFFFF;
    --verify-surface-soft: #F1F5F9;
    --verify-border: #E5E7EB;
    --verify-text: #111827;
    --verify-muted: #4B5563;
    --verify-accent: #2563EB;
    --verify-accent-hover: #1D4ED8;
    --verify-accent-text: #FFFFFF;
}

.email-verify-surface {
    background: var(--verify-surface) !important;
    border-color: var(--verify-border) !important;
}

.email-verify-surface-soft {
    background: var(--verify-surface-soft) !important;
}

.email-verify-text {
    color: var(--verify-text) !important;
}

.email-verify-muted {
    color: var(--verify-muted) !important;
}

.email-verify-accent-bg {
    background-color: var(--verify-accent) !important;
    color: var(--verify-accent-text) !important;
}

.email-verify-accent-bg:hover {
    background-color: var(--verify-accent-hover) !important;
}

.email-verify-btn-secondary {
    border-color: var(--verify-border) !important;
    color: var(--verify-muted) !important;
    background: transparent !important;
}

.email-verify-btn-secondary:hover {
    border-color: var(--verify-accent) !important;
    color: var(--verify-accent) !important;
}

.email-verify-alert-success {
    border-color: rgba(16, 185, 129, 0.28) !important;
    background: #ECFDF5 !important;
    color: #047857 !important;
}

html[data-theme-resolved="dark"] .email-verify-alert-success {
    background: rgba(16, 185, 129, 0.12) !important;
    color: #A7F3D0 !important;
}
</style>
@endpush

@section('content')
    <div class="email-verify-page min-h-[80vh] flex items-center justify-center py-12">
        <div class="relative w-full max-w-3xl px-4">
            <div class="email-verify-surface overflow-hidden rounded-3xl border shadow-2xl">
                <div class="grid md:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
                    <div class="p-6 sm:p-8 flex flex-col justify-center">
                        <h2 class="text-2xl font-bold tracking-tight email-verify-text">Verifikasi Email</h2>
                        <p class="mt-2 text-sm email-verify-muted leading-relaxed">
                            Sebelum melanjutkan pemesanan, verifikasi email kamu lewat tautan yang sudah dikirim.
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-4 rounded-xl border px-4 py-3 text-sm email-verify-alert-success">
                                Link verifikasi baru sudah dikirim. Cek inbox atau spam email kamu.
                            </div>
                        @endif

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button class="email-verify-accent-bg inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                    Kirim Ulang Email Verifikasi
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="email-verify-btn-secondary inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="email-verify-surface-soft hidden p-8 md:flex md:flex-col md:justify-center border-l border-[var(--verify-border)]">
                        <div class="mb-6">
                            <x-brand.image light="manake-logo-blue.png" dark="manake-logo-white.png" alt="Manake" img-class="h-10 w-auto" />
                        </div>
                        <h3 class="text-xl font-bold leading-tight email-verify-text">Cek email untuk aktivasi akun.</h3>
                        <p class="mt-3 text-sm email-verify-muted leading-relaxed">
                            Setelah email terverifikasi, kamu bisa melengkapi data penyewa dan melanjutkan pemesanan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

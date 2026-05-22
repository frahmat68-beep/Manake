@extends('layouts.app')

@section('title', 'Verifikasi Email | Manake')

@section('content')
    <div class="relative min-h-[70vh]">
        <div class="absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-[#0A0A0B] via-[#111113] to-[#0A0A0B]"></div>

        <div class="mx-auto flex min-h-[70vh] max-w-3xl items-center justify-center px-2">
            <div class="w-full max-w-2xl overflow-hidden rounded-lg border border-[#1A1A1E] bg-[#111113] shadow-2xl">
                <div class="grid md:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-2xl font-semibold text-[#E8E8EC]">Verifikasi Email</h2>
                        <p class="mt-2 text-sm text-[#A0A0A8]">
                            Sebelum lanjut pembayaran, verifikasi dulu email kamu lewat link yang sudah dikirim.
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-4 rounded-md border border-emerald-500/20 bg-emerald-950/70 px-4 py-3 text-sm text-emerald-300">
                                Link verifikasi baru sudah dikirim. Cek inbox atau spam email kamu.
                            </div>
                        @endif

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-md bg-[#D4A843] px-4 py-2.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                    Kirim Ulang Email Verifikasi
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-[#1A1A1E] px-4 py-2.5 text-sm font-semibold text-[#A0A0A8] transition hover:border-[#D4A843]/40 hover:text-[#D4A843]">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="hidden bg-gradient-to-br from-[#0A0A0B] via-[#111113] to-[#0A0A0B] p-8 text-[#E8E8EC] md:block">
                        <x-brand.image light="manake-logo-blue.png" dark="manake-logo-blue.png" alt="Manake" img-class="h-12 w-auto" />
                        <h3 class="mt-6 text-2xl font-semibold leading-tight">Cek email untuk aktivasi akun.</h3>
                        <p class="mt-3 text-sm text-[#A0A0A8]">
                            Setelah verifikasi selesai, kamu bisa lanjut isi profil, pembayaran, dan pantau progres pesanan secara realtime.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

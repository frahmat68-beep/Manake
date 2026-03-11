@extends('layouts.app')

@section('title', 'Verifikasi Email | Manake')

@section('content')
    <div class="relative min-h-[70vh]">
        <div class="absolute inset-0 -z-10 rounded-3xl bg-gradient-to-br from-slate-100 via-blue-50 to-slate-100"></div>

        <div class="mx-auto flex min-h-[70vh] max-w-3xl items-center justify-center px-2">
            <div class="w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="grid md:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-2xl font-semibold text-slate-900">Verifikasi Email</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Sebelum lanjut pembayaran, verifikasi dulu email kamu lewat link yang sudah dikirim.
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                Link verifikasi baru sudah dikirim. Cek inbox atau spam email kamu.
                            </div>
                        @endif

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    Kirim Ulang Email Verifikasi
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="hidden bg-gradient-to-br from-slate-950 via-blue-900 to-slate-900 p-8 text-white md:block">
                        <img src="{{ site_asset('manake-logo-blue.png') }}" alt="Manake" class="h-12 w-auto rounded-xl bg-white p-2">
                        <h3 class="mt-6 text-2xl font-semibold leading-tight">Cek email untuk aktivasi akun.</h3>
                        <p class="mt-3 text-sm text-blue-100">
                            Setelah verifikasi selesai, kamu bisa lanjut isi profil, pembayaran, dan pantau progres pesanan secara realtime.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

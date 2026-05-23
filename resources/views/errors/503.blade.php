@extends('layouts.landing')

@section('title', __('Manake Maintenance'))
@section('meta_description', __('Manake sedang melakukan maintenance singkat.'))

@section('content')
    <main class="manake-page">
        <section class="manake-section flex min-h-[70vh] items-center">
            <div class="manake-page-frame">
                <div class="mx-auto max-w-2xl rounded-lg border border-[#1A1A1E] bg-[#111113] p-8 text-center shadow-2xl sm:p-12">
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-[#D4A843]">{{ __('Maintenance') }}</p>
                    <h1 class="mt-4 text-3xl font-black tracking-tight text-[#E8E8EC] sm:text-5xl">
                        {{ __('Manake sedang disiapkan sebentar.') }}
                    </h1>
                    <p class="mt-4 text-sm font-semibold leading-relaxed text-[#A0A0A8] sm:text-base">
                        {{ __('Kami sedang merapikan sistem, inventaris, dan alur sewa agar pengalaman booking tetap stabil.') }}
                    </p>
                    <a href="{{ route('home') }}" class="mt-8 inline-flex items-center justify-center rounded-md bg-[#D4A843] px-6 py-3 text-sm font-black text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                        {{ __('Coba Lagi') }}
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection

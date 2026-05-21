@extends('layouts.app')

@section('title', __('Katalog Alat') . ' | Manake')

@section('content')
    <div class="manake-page">
        <div class="manake-page-frame space-y-6">
            <section class="manake-card manake-section animate-fade-up">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="manake-kicker">{{ __('Katalog') }}</p>
                        <h1 class="manake-display mt-3 text-4xl font-black text-slate-950 dark:text-white sm:text-5xl">
                            {{ __('Katalog Alat') }}
                        </h1>
                        <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base">
                            {{ __('Cari kamera, drone, lighting, audio, dan gear produksi dengan tampilan yang lebih fokus dan mudah dipindai.') }}
                        </p>
                    </div>

                    <div class="w-full max-w-xl">
                        <label class="sr-only" for="catalog-search">{{ __('Cari alat') }}</label>
                        <input
                            id="catalog-search"
                            type="text"
                            placeholder="{{ __('Cari kamera, drone, lighting...') }}"
                            class="manake-input"
                        >
                    </div>
                </div>
            </section>

            <section class="manake-card manake-section">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="manake-kicker">{{ __('Mulai eksplor') }}</p>
                        <h2 class="manake-heading mt-2 text-2xl font-black text-slate-950 dark:text-white">
                            {{ __('Daftar alat produksi') }}
                        </h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="manake-badge manake-badge-info">{{ __('Ready now') }}</span>
                        <span class="manake-badge manake-badge-success">{{ __('Fast booking') }}</span>
                        <span class="manake-badge manake-badge-warning">{{ __('Verified stock') }}</span>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="manake-card-soft p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('Kategori populer') }}</p>
                        <p class="mt-2 text-lg font-black text-slate-950 dark:text-white">{{ __('Camera, lighting, audio') }}</p>
                    </div>
                    <div class="manake-card-soft p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('Fokus pencarian') }}</p>
                        <p class="mt-2 text-lg font-black text-slate-950 dark:text-white">{{ __('Availability + detail alat') }}</p>
                    </div>
                    <div class="manake-card-soft p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('Langkah berikutnya') }}</p>
                        <p class="mt-2 text-lg font-black text-slate-950 dark:text-white">{{ __('Buka product detail') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

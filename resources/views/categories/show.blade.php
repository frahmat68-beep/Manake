@extends('layouts.landing')

@section('title', $category->name . ' - ' . config('app.name'))

@section('content')
    <section class="mk-section bg-slate-50/50 dark:bg-slate-950/20">
        <div class="mk-container">
        {{-- Breadcrumb --}}
        <nav class="mb-10 flex text-xs font-black uppercase tracking-widest" aria-label="{{ __('Breadcrumb') }}">
            <ol class="inline-flex items-center space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        {{ __('Home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('catalog') }}" class="ml-2 text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ __('Katalog') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-slate-300 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-blue-600 dark:text-blue-400">{{ $category->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Category Header --}}
        <header class="mb-16 text-center">
            <h1 class="mk-title-hero">
                {{ $category->name }}
            </h1>
            <p class="mk-copy mx-auto mt-6 max-w-2xl">
                {{ $category->description ?: __('Jelajahi koleksi peralatan profesional terbaik kami untuk mendukung visi kreatif Anda.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-950/40 px-6 py-2 text-xs font-black uppercase tracking-wider text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                    {{ $products->count() }} {{ __('Alat Tersedia') }}
                </span>
            </div>
        </header>

        {{-- Equipment Grid --}}
        @if ($products->count() > 0)
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $item)
                    <article class="mk-card group flex flex-col overflow-hidden cursor-pointer" onclick="window.location.assign('{{ route('product.show', $item->slug) }}')">
                        {{-- Image Container --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-50/50 dark:bg-slate-900/30 p-6 flex items-center justify-center border-b border-slate-100 dark:border-slate-800/60">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                            <img 
                                src="{{ $item->image_path ? site_media_url($item->image_path) : config('placeholders.equipment') }}" 
                                alt="{{ $item->name }}" 
                                class="h-full w-full object-contain transition-transform duration-700 group-hover:scale-110 drop-shadow-xl"
                            >
                            
                            {{-- Availability Chip --}}
                            @php
                                $stock = (int) $item->stock;
                                $reservedUnits = (int) ($item->active_order_items_sum_qty ?? 0);
                                $available = max(0, $stock - $reservedUnits);
                            @endphp
                            <div class="absolute left-5 top-5">
                                @if($available > 0)
                                    <span class="rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-500/10">
                                        {{ __('Tersedia') }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-rose-500/90 dark:bg-rose-600/90 backdrop-blur-md px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm border border-rose-500/10">
                                        {{ __('Tersewa') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Info Container --}}
                        <div class="flex flex-1 flex-col p-6 sm:p-7">
                            <h3 class="text-xl font-bold leading-tight text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 min-h-[3.5rem] line-clamp-2">
                                <a href="{{ route('product.show', $item->slug) }}">
                                    {{ $item->name }}
                                </a>
                            </h3>
                            
                            <div class="mt-6 flex items-end justify-between gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ __('Mulai dari') }}</p>
                                    <p class="mt-1 text-2xl font-black text-slate-955 dark:text-slate-50">
                                        Rp{{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-xs text-slate-400 dark:text-slate-500 font-bold ml-1">/{{ __('hari') }}</span>
                                    </p>
                                </div>
                                <a href="{{ route('product.show', $item->slug) }}" class="mk-button-primary !p-3 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-20">
                {{ $equipments->links() }}
            </div>
        @else
            <article class="mk-card flex flex-col items-center justify-center py-24 px-8 text-center">
                <div class="relative mb-8">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/55 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="mk-title-section text-slate-900 dark:text-slate-100">{{ __('Alat Belum Tersedia') }}</h2>
                <p class="mk-copy mt-4 max-w-sm">{{ __('Maaf, saat ini belum ada alat dalam kategori ini. Silakan periksa kategori lainnya.') }}</p>
                <a href="{{ route('catalog') }}" class="mk-button-primary mt-8">
                    {{ __('Kembali ke Katalog') }}
                </a>
            </article>
        @endif
        </div>
    </section>
@endsection

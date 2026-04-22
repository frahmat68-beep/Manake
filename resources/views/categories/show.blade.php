@extends('layouts.app')

@section('title', $category->name . ' - ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-10 flex text-xs font-black uppercase tracking-widest" aria-label="{{ __('Breadcrumb') }}">
            <ol class="inline-flex items-center space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-slate-400 hover:text-blue-700 transition-colors">
                        {{ __('Home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('catalog') }}" class="ml-2 text-slate-400 hover:text-blue-700 transition-colors">{{ __('Katalog') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-blue-700">{{ $category->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Category Header --}}
        <header class="mb-16 text-center">
            <h1 class="text-5xl font-black tracking-tight text-slate-900 md:text-7xl">
                {{ $category->name }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg font-medium leading-relaxed text-slate-500">
                {{ $category->description ?: __('Jelajahi koleksi peralatan profesional terbaik kami untuk mendukung visi kreatif Anda.') }}
            </p>
            <div class="mt-8 flex justify-center">
                <span class="inline-flex items-center rounded-3xl bg-blue-50 px-6 py-2 text-sm font-black text-blue-700 border border-blue-100">
                    {{ $products->count() }} {{ __('Alat Tersedia') }}
                </span>
            </div>
        </header>

        {{-- Equipment Grid --}}
        @if ($products->count() > 0)
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $item)
                    <article class="group relative flex flex-col overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-100">
                        {{-- Image Container --}}
                        <div class="relative aspect-[4/5] overflow-hidden bg-slate-50 border-b border-slate-100">
                            <img 
                                src="{{ $item->image_path ? site_media_url($item->image_path) : config('placeholders.equipment') }}" 
                                alt="{{ $item->name }}" 
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            
                            {{-- Availability Chip --}}
                            @php
                                $stock = (int) $item->stock;
                                $reservedUnits = (int) ($item->active_order_items_sum_qty ?? 0);
                                $available = max(0, $stock - $reservedUnits);
                            @endphp
                            <div class="absolute left-5 top-5">
                                @if($available > 0)
                                    <span class="rounded-2xl bg-white/95 backdrop-blur-sm px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600 shadow-sm">
                                        {{ __('Tersedia') }}
                                    </span>
                                @else
                                    <span class="rounded-2xl bg-rose-500/95 backdrop-blur-sm px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                                        {{ __('Tersewa') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Info Container --}}
                        <div class="flex flex-1 flex-col p-6 sm:p-8">
                            <h3 class="text-xl font-extrabold text-slate-900 line-clamp-2 min-h-[3.5rem] group-hover:text-blue-700 transition-colors">
                                <a href="{{ route('product.show', $item->slug) }}">
                                    {{ $item->name }}
                                </a>
                            </h3>
                            
                            <div class="mt-6 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('Mulai dari') }}</p>
                                    <p class="mt-1 text-2xl font-black text-slate-900">
                                        Rp{{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-xs text-slate-400 font-bold ml-1">/hari</span>
                                    </p>
                                </div>
                                <a href="{{ route('product.show', $item->slug) }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white transition-all duration-300 hover:bg-blue-700 hover:scale-110 active:scale-95 shadow-lg shadow-slate-200">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <article class="flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-slate-200 bg-white py-32 text-center">
                <div class="relative mb-8">
                    <div class="absolute -inset-6 rounded-full bg-slate-50 opacity-50"></div>
                    <svg class="relative h-24 w-24 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-slate-900">{{ __('Alat Belum Tersedia') }}</h2>
                <p class="mt-4 max-w-sm text-lg text-slate-500 font-medium">{{ __('Maaf, saat ini belum ada alat dalam kategori ini. Silakan periksa kategori lainnya.') }}</p>
                <a href="{{ route('catalog') }}" class="btn-primary mt-10 rounded-2xl px-12 py-4 font-bold shadow-xl shadow-blue-200 transition-all hover:scale-105 active:scale-95">
                    {{ __('Kembali ke Katalog') }}
                </a>
            </article>
        @endif
    </div>
@endsection

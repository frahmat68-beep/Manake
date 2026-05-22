@extends('layouts.landing')

@section('title', $category->name . ' - ' . config('app.name'))

@php
    $resolvePublicStatusLabel = static function (string $statusValue, int $availableUnits): string {
        $normalized = strtolower(trim($statusValue));

        return match ($normalized) {
            'maintenance' => 'Maintenance',
            'unavailable' => 'Tidak Tersedia',
            'ready' => $availableUnits > 0 ? 'Tersedia' : 'Penuh / Sedang Disewa',
            default => $availableUnits > 0 ? 'Tersedia' : 'Tidak Tersedia',
        };
    };
    $resolvePublicStatusClass = static function (string $statusValue, int $availableUnits): string {
        $normalized = strtolower(trim($statusValue));

        return match ($normalized) {
            'maintenance' => 'border-amber-500/20 bg-amber-950/75 text-amber-300',
            'unavailable' => 'border-rose-400/30 bg-rose-950/75 text-rose-300',
            'ready' => $availableUnits > 0
                ? 'border-emerald-400/30 bg-emerald-950/75 text-emerald-300'
                : 'border-amber-400/30 bg-amber-950/75 text-amber-200',
            default => $availableUnits > 0
                ? 'border-emerald-400/30 bg-emerald-950/75 text-emerald-300'
                : 'border-rose-400/30 bg-rose-950/75 text-rose-300',
        };
    };
@endphp

@section('content')
    <section class="mk-section bg-[#0A0A0B] text-[#E8E8EC]">
        <div class="mk-container">
        {{-- Breadcrumb --}}
        <nav class="mb-10 flex text-xs font-black uppercase tracking-widest" aria-label="{{ __('Breadcrumb') }}">
            <ol class="inline-flex items-center space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="text-[#A0A0A8] hover:text-[#D4A843] transition-colors">
                        {{ __('Home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-[#2A2A2F]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('catalog') }}" class="ml-2 text-[#A0A0A8] hover:text-[#D4A843] transition-colors">{{ __('Katalog') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="h-3 w-3 text-[#2A2A2F]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-[#D4A843]">{{ $category->name }}</span>
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
            <span class="inline-flex items-center rounded-full border border-[#D4A843]/20 bg-[#111113] px-6 py-2 text-xs font-black uppercase tracking-wider text-[#D4A843]">
                    {{ $products->count() }} {{ __('Alat Tersedia') }}
                </span>
            </div>
        </header>

        {{-- Equipment Grid --}}
        @if ($products->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($products as $item)
                    <article class="group flex flex-col overflow-hidden cursor-pointer rounded-lg border border-[#1A1A1E] bg-[#111113] transition hover:-translate-y-1 hover:border-[#D4A843]/20" onclick="window.location.assign('{{ route('product.show', $item->slug) }}')">
                        {{-- Image Container --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-[#0A0A0B] p-6 flex items-center justify-center border-b border-[#1A1A1E]">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A843]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                            <img 
                                src="{{ site_media_url($item->image_path ?: $item->image) ?: config('placeholders.equipment') }}" 
                                alt="{{ $item->name }}" 
                                class="h-full w-full object-contain transition-transform duration-700 group-hover:scale-110 drop-shadow-xl"
                                onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                            >
                            
                            {{-- Availability Chip --}}
                            @php
                                $stock = (int) $item->stock;
                                $reservedUnits = (int) ($item->active_order_items_sum_qty ?? 0);
                                $available = max(0, $stock - $reservedUnits);
                                $statusValue = (string) ($item->status ?? ($available > 0 ? 'ready' : 'unavailable'));
                                $statusLabel = $resolvePublicStatusLabel($statusValue, $available);
                                $statusClass = $resolvePublicStatusClass($statusValue, $available);
                            @endphp
                            <div class="absolute left-5 top-5">
                                <span class="rounded-full backdrop-blur-md px-3 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        {{-- Info Container --}}
                        <div class="flex flex-1 flex-col p-6 sm:p-7">
                            <h3 class="text-xl font-bold leading-tight text-[#E8E8EC] group-hover:text-[#D4A843] transition-colors duration-300 min-h-[3.5rem] line-clamp-2">
                                <a href="{{ route('product.show', $item->slug) }}">
                                    {{ $item->name }}
                                </a>
                            </h3>
                            
                            <div class="mt-6 flex items-end justify-between gap-4 border-t border-[#1A1A1E] pt-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#A0A0A8]">{{ __('Mulai dari') }}</p>
                                    <p class="mt-1 text-2xl font-black text-[#E8E8EC]">
                                        Rp{{ number_format($item->price_per_day, 0, ',', '.') }}<span class="text-xs text-[#A0A0A8] font-bold ml-1">/{{ __('hari') }}</span>
                                    </p>
                                </div>
                                <a href="{{ route('product.show', $item->slug) }}" class="inline-flex items-center justify-center rounded-md bg-[#D4A843] p-3 text-[#0A0A0B] transition hover:bg-[#e0ba5d] shrink-0">
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
            <article class="flex flex-col items-center justify-center rounded-lg border border-[#1A1A1E] bg-[#111113] py-24 px-8 text-center">
                <div class="relative mb-8">
                    <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-[#0A0A0B] text-[#D4A843] border border-[#1A1A1E]">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-black text-[#E8E8EC]">{{ __('Alat Belum Tersedia') }}</h2>
                <p class="mt-4 max-w-sm text-sm text-[#A0A0A8]">{{ __('Maaf, saat ini belum ada alat dalam kategori ini. Silakan periksa kategori lainnya.') }}</p>
                <a href="{{ route('catalog') }}" class="mt-8 inline-flex items-center rounded-md bg-[#D4A843] px-5 py-3 text-sm font-black text-[#0A0A0B]">
                    {{ __('Kembali ke Katalog') }}
                </a>
            </article>
        @endif
        </div>
    </section>
@endsection

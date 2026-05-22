@extends('layouts.landing')

@section('title', $category->name . ' - ' . site_setting('brand.name', config('app.name', 'Manake')))

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
    <section class="bg-[#0A0A0B] text-[#E8E8EC]">
        <div class="mx-auto max-w-7xl px-6 py-16 md:px-10 md:py-20">
            <nav class="mb-10 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-[#A0A0A8]" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-[#D4A843]">{{ __('Home') }}</a>
                <span class="text-[#2A2A2F]">/</span>
                <a href="{{ route('catalog') }}" class="transition hover:text-[#D4A843]">{{ __('Katalog') }}</a>
                <span class="text-[#2A2A2F]">/</span>
                <span class="text-[#D4A843]">{{ $category->name }}</span>
            </nav>

            <header class="max-w-3xl">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.28em] text-[#D4A843]">{{ __('Kategori Peralatan') }}</p>
                <h1 class="text-[clamp(2.8rem,5vw,4.8rem)] leading-[0.96] tracking-[-0.04em] text-[#E8E8EC]" style="font-family: 'DM Serif Display', Georgia, serif;">
                    {{ $category->name }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-[#A0A0A8] md:text-lg">
                    {{ $category->description ?: __('Jelajahi koleksi peralatan profesional terbaik kami untuk mendukung visi kreatif Anda.') }}
                </p>
                <div class="mt-8 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-[#E8E8EC]">
                    <span class="h-2 w-2 rounded-full bg-[#D4A843]"></span>
                    {{ $products->count() }} {{ __('alat tersedia') }}
                </div>
            </header>

            @if ($products->count() > 0)
                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $item)
                        @php
                            $image = site_media_url($item->image_path ?: $item->image) ?: site_asset('MANAKE-FAV-M.png');
                            $stock = (int) $item->stock;
                            $reservedUnits = (int) ($item->active_order_items_sum_qty ?? 0);
                            $available = max(0, $stock - $reservedUnits);
                            $statusValue = (string) ($item->status ?? ($available > 0 ? 'ready' : 'unavailable'));
                            $statusLabel = $resolvePublicStatusLabel($statusValue, $available);
                            $statusClass = $resolvePublicStatusClass($statusValue, $available);
                        @endphp
                        <article class="group flex h-full flex-col overflow-hidden rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] shadow-[0_18px_50px_-36px_rgba(0,0,0,0.9)] transition hover:-translate-y-1 hover:border-[#D4A843]/20">
                            <a href="{{ route('product.show', $item->slug) }}" class="relative aspect-[4/3] overflow-hidden bg-[#0A0A0B]">
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $item->name }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                    onerror="this.onerror=null;this.src='{{ site_asset('MANAKE-FAV-M.png') }}';"
                                >
                                <div class="absolute left-4 top-4 rounded-full border border-white/10 bg-black/70 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-[#E8E8EC]">
                                    {{ $item->category?->name ?? __('Peralatan') }}
                                </div>
                                <div class="absolute right-4 top-4 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </div>
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                <div>
                                    <h2 class="text-lg font-semibold leading-snug text-[#E8E8EC]">{{ $item->name }}</h2>
                                    <p class="mt-1 text-xs text-[#A0A0A8]">{{ $category->name }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-between rounded-2xl border border-white/8 bg-black/20 px-4 py-3">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Mulai dari') }}</p>
                                        <p class="mt-1 text-base font-semibold text-[#E8E8EC]">
                                            Rp {{ number_format($item->price_per_day, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase tracking-[0.22em] text-[#A0A0A8]">{{ __('Sisa stok') }}</p>
                                        <p class="mt-1 text-base font-semibold {{ $available > 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                            {{ $available }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('product.show', $item->slug) }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-[#D4A843] px-4 py-3 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d]">
                                    {{ __('Lihat Detail') }}
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $equipments->links() }}
                </div>
            @else
                <article class="mt-12 flex flex-col items-center justify-center rounded-[1.35rem] border border-[#1A1A1E] bg-[#111113] px-8 py-24 text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] text-[#D4A843]">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-2xl font-semibold text-[#E8E8EC]">{{ __('Alat Belum Tersedia') }}</h2>
                    <p class="mt-3 max-w-sm text-sm leading-7 text-[#A0A0A8]">{{ __('Maaf, saat ini belum ada alat dalam kategori ini. Silakan periksa kategori lainnya.') }}</p>
                    <a href="{{ route('catalog') }}" class="mt-8 inline-flex items-center rounded-md bg-[#D4A843] px-5 py-3 text-sm font-semibold text-[#0A0A0B]">
                        {{ __('Kembali ke Katalog') }}
                    </a>
                </article>
            @endif
        </div>
    </section>
@endsection

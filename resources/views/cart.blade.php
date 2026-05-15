<x-app-layout>
    @section('title', __('Keranjang Belanja'))

    <div class="bg-slate-50 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 py-12 pb-24 sm:px-6 lg:px-8">
            {{-- Header Section --}}
            <header class="mb-12 text-center sm:text-left animate-fade-up">
                <div class="glass-lg noise-overlay spotlight-shell rounded-[2.5rem] p-8 sm:p-10 border border-white/20 shadow-2xl">
                    <h1 class="text-4xl font-black tracking-tight text-slate-950 sm:text-5xl leading-tight">
                        {{ __('Keranjang Belanja') }}
                    </h1>
                    <p class="mt-4 text-lg text-slate-600 font-medium max-w-2xl leading-relaxed">
                        {{ __('Kelola pilihan alat produksi Anda sebelum melanjutkan ke pembayaran.') }}
                    </p>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:items-start lg:gap-16">
                {{-- Cart Items Section --}}
                <section class="lg:col-span-12 space-y-8">
                    @if (session('success'))
                        <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-6 py-4 text-sm font-bold text-emerald-600 shadow-sm animate-fade-in-down flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-6 py-4 text-sm font-bold text-rose-600 shadow-sm animate-fade-in-down flex items-center gap-3">
                            <div class="h-2 w-2 rounded-full bg-rose-500"></div>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (empty($cartItems))
                        <article class="flex flex-col items-center justify-center rounded-[3.5rem] border-2 border-dashed border-slate-200 bg-white/50 backdrop-blur-sm py-24 text-center animate-fade-up">
                            <div class="relative mb-8">
                                <div class="absolute -inset-8 rounded-full bg-blue-500/10 animate-pulse"></div>
                                <div class="relative h-24 w-24 rounded-[2rem] bg-white flex items-center justify-center shadow-2xl border border-slate-100">
                                    <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                            </div>
                            <h2 class="text-3xl font-black text-slate-900">{{ __('Keranjang Kosong') }}</h2>
                            <p class="mt-3 max-w-sm text-slate-500 font-semibold text-lg">{{ __('Anda belum menambahkan item apapun ke keranjang belanja.') }}</p>
                            <a href="{{ route('catalog') }}" class="btn-primary mt-10 inline-flex items-center rounded-2xl px-12 py-4 font-black tracking-widest uppercase text-xs shadow-2xl shadow-blue-600/30">
                                {{ __('Mulai Sewa Alat') }}
                            </a>
                        </article>
                    @else
                        <div class="space-y-6">
                            @foreach ($cartItems as $item)
                            <article class="premium-card noise-overlay spotlight-shell group relative overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/40 p-6 transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl sm:p-8 animate-fade-up" style="animation-delay: {{ $loop->index * 50 }}ms">
                                <div class="flex flex-col gap-8 sm:flex-row sm:items-center">
                                    {{-- Thumbnail --}}
                                    <div class="h-40 w-40 flex-shrink-0 overflow-hidden rounded-[2rem] bg-white border border-slate-100 shadow-lg group-hover:shadow-xl transition-all duration-500">
                                        <img src="{{ $item['image'] ?? config('placeholders.equipment') }}" alt="{{ $item['name'] }}" class="h-full w-full object-contain p-4 transition-transform duration-700 group-hover:scale-110">
                                    </div>

                                    {{-- Details --}}
                                    <div class="flex flex-1 flex-col justify-between">
                                        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
                                            <div class="max-w-xl">
                                                <span class="mb-2 inline-block text-[10px] font-black uppercase tracking-[0.2em] text-blue-600/70">
                                                    {{ $item['category'] ?? __('Umum') }}
                                                </span>
                                                <h3 class="text-2xl font-black text-slate-950 group-hover:text-blue-700 transition-colors leading-tight">
                                                    <a href="{{ route('product.show', $item['slug'] ?? '#') }}">{{ $item['name'] }}</a>
                                                </h3>
                                                @if (!empty($item['rental_start_date']) && !empty($item['rental_end_date']))
                                                    <div class="mt-4 flex flex-wrap items-center gap-3 text-[13px] font-bold text-slate-500 bg-slate-100/50 w-fit px-4 py-2 rounded-xl border border-slate-200/50">
                                                        <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span class="text-slate-900">{{ Carbon\Carbon::parse($item['rental_start_date'])->translatedFormat('d M Y') }}</span>
                                                        <span class="text-slate-300">—</span>
                                                        <span class="text-slate-900">{{ Carbon\Carbon::parse($item['rental_end_date'])->translatedFormat('d M Y') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-left sm:text-right">
                                                <p class="text-2xl font-black text-slate-950 tracking-tighter">
                                                    @php
                                                        $itemPrice = (int)($item['price'] ?? 0);
                                                        $itemQty = (int)($item['qty'] ?? 1);
                                                        $days = 1;
                                                        if (!empty($item['rental_start_date']) && !empty($item['rental_end_date'])) {
                                                            $days = Carbon\Carbon::parse($item['rental_start_date'])->diffInDays(Carbon\Carbon::parse($item['rental_end_date'])) + 1;
                                                        }
                                                        $lineSubtotal = $itemPrice * $itemQty * $days;
                                                    @endphp
                                                    Rp {{ number_format($lineSubtotal, 0, ',', '.') }}
                                                </p>
                                                <p class="mt-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                                    Rp {{ number_format($itemPrice, 0, ',', '.') }} × {{ $itemQty }} unit × {{ $days }} hari
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-10 flex items-center justify-between gap-6">
                                            {{-- Quantity Selector --}}
                                            <div class="inline-flex items-center rounded-2xl bg-white/60 p-1 border border-slate-200 shadow-sm">
                                                <form method="POST" action="{{ route('cart.decrement', $item['key']) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-600 border border-slate-100 shadow-sm transition hover:text-blue-700 active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed" {{ $itemQty <= 1 ? 'disabled' : '' }}>
                                                        <span class="text-lg font-black">−</span>
                                                    </button>
                                                </form>
                                                <span class="w-14 text-center text-base font-black text-slate-900">{{ $itemQty }}</span>
                                                <form method="POST" action="{{ route('cart.increment', $item['key']) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-600 border border-slate-100 shadow-sm transition hover:text-blue-700 active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed" {{ $itemQty >= ($item['stock'] ?? 99) ? 'disabled' : '' }}>
                                                        <span class="text-lg font-black">+</span>
                                                    </button>
                                                </form>
                                            </div>

                                            {{-- Remove Action --}}
                                            <form method="POST" action="{{ route('cart.remove', $item['key']) }}" 
                                                class="inline"
                                                x-data
                                                @submit.prevent="if(confirm('{{ __('Hapus item ini dari keranjang?') }}')) $el.submit()">
                                                @csrf @method('DELETE')
                                                <button class="flex items-center gap-2.5 px-6 py-3.5 rounded-2xl bg-rose-500/5 text-[10px] font-black uppercase tracking-[0.2em] text-rose-500 transition-all hover:bg-rose-500/10 group/btn border border-rose-500/10">
                                                    <svg class="h-4 w-4 transition-transform group-hover/btn:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    {{ __('Hapus') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if (!empty($cartItems))
                {{-- Summary Section --}}
                <section class="lg:col-span-12 mt-8">
                    <article class="premium-card noise-overlay spotlight-shell relative overflow-hidden rounded-[3rem] border border-slate-800 bg-slate-950 p-10 shadow-2xl sm:p-12 text-white animate-fade-up">
                        <div class="absolute top-0 right-0 p-12 opacity-10 blur-2xl">
                            <div class="h-64 w-64 rounded-full bg-blue-500"></div>
                        </div>
                        
                        <div class="relative z-10">
                            <h2 class="text-3xl font-black tracking-tight leading-none">{{ __('Ringkasan Biaya Estimasi') }}</h2>
                            <p class="mt-3 text-slate-400 font-bold text-base">{{ __('Semua biaya sudah termasuk pajak dan garansi alat dasar.') }}</p>
                            
                            <div class="mt-12 space-y-6 border-t border-slate-800/50 pt-12">
                                @if ($cartSuggestedStartDate && $cartSuggestedEndDate)
                                    <div class="flex items-center justify-between rounded-3xl bg-blue-600/10 p-6 border border-blue-500/20 shadow-inner">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest block mb-0.5">{{ __('Masa Sewa Terpusat') }}</span>
                                                <span class="text-lg font-black">{{ Carbon\Carbon::parse($cartSuggestedStartDate)->format('d M') }} — {{ Carbon\Carbon::parse($cartSuggestedEndDate)->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <span class="hidden sm:inline-block glass-sm px-4 py-2 rounded-xl text-xs font-bold text-blue-400 border border-blue-500/10">{{ $days }} Hari Produksi</span>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6">
                                    <div class="space-y-4">
                                        <div class="flex justify-between text-base font-bold text-slate-400">
                                            <span>{{ __('Subtotal') }}</span>
                                            <span class="text-white">Rp {{ number_format($estimatedSubtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between text-base font-bold text-slate-400">
                                            <span>{{ __('PPN (11%)') }}</span>
                                            <span class="text-white">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-start sm:items-end justify-center">
                                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-500 mb-2">{{ __('Total Pembayaran') }}</span>
                                        <p class="text-5xl font-black lg:text-6xl tracking-tighter">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-6 border-t border-slate-800/50 pt-10 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04default_api:6A11.955 11.955 0 013 12c0 5.391 3.991 9.928 9 10.822 5.009-.894 9-5.43 9-10.822 0-2.08-.528-4.047-1.455-5.764z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Keamanan Pembayaran') }}</p>
                                            <p class="text-sm font-bold text-slate-300">Diproses oleh <span class="text-white font-black">Midtrans</span></p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-4 min-w-[320px]">
                                        <a href="{{ route('checkout') }}" class="btn-primary group/checkout relative overflow-hidden flex items-center justify-center rounded-2xl py-5 text-lg font-black shadow-2xl shadow-blue-600/40 transition-all hover:scale-[1.02] active:scale-95">
                                            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover/checkout:opacity-100 transition-opacity"></div>
                                            {{ __('Lanjut ke Checkout') }}
                                            <svg class="ml-3 h-5 w-5 transition-transform group-hover/checkout:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
                @endif

                {{-- Recommendations --}}
                @if ($suggestedEquipments->isNotEmpty())
                <aside class="lg:col-span-12 mt-16 animate-fade-up">
                    <div class="mb-10 flex items-end justify-between px-4">
                        <div>
                            <h2 class="text-3xl font-black text-slate-950 tracking-tight">{{ __('Lengkapi Produksi Anda') }}</h2>
                            <p class="mt-2 text-base text-slate-500 font-bold">{{ __('Beberapa alat yang mungkin Anda butuhkan sebagai pendukung.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($suggestedEquipments as $suggestion)
                        <article class="premium-card noise-overlay spotlight-shell group relative flex flex-col rounded-[2.5rem] border border-white/20 bg-white/40 p-6 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <div class="relative mb-6 aspect-[4/3] overflow-hidden rounded-[1.75rem] bg-white border border-slate-100 shadow-sm">
                                <img src="{{ $suggestion->image_path ? site_media_url($suggestion->image_path) : config('placeholders.equipment') }}" alt="{{ $suggestion->name }}" class="h-full w-full object-contain p-4 transition-transform duration-700 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 opacity-0 transition-opacity group-hover:opacity-100"></div>
                            </div>
                            <span class="mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-blue-600/70">
                                {{ $suggestion->category->name ?? __('Umum') }}
                            </span>
                            <h3 class="line-clamp-1 text-lg font-black text-slate-950 transition-colors group-hover:text-blue-700 leading-tight">
                                <a href="{{ route('product.show', $suggestion->slug) }}">{{ $suggestion->name }}</a>
                            </h3>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-xl font-black text-slate-950 tracking-tighter">
                                    Rp {{ number_format($suggestion->price_per_day, 0, ',', '.') }}<span class="ml-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">/ hari</span>
                                </p>
                            </div>
                            <a href="{{ route('product.show', $suggestion->slug) }}" class="mt-6 flex items-center justify-center rounded-2xl bg-white/80 py-4 text-[11px] font-black uppercase tracking-widest text-slate-700 transition-all hover:bg-blue-600 hover:text-white border border-slate-200 hover:border-blue-600 hover:shadow-xl hover:shadow-blue-600/20 active:scale-95">
                                {{ __('Tambah Cepat') }}
                            </a>
                        </article>
                        @endforeach
                    </div>
                </aside>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

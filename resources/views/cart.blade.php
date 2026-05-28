<x-app-layout>
    @section('title', __('Keranjang Belanja'))

    @php
        $cartItemCount = count($cartItems);
        $cartUnitCount = collect($cartItems)->sum(fn (array $item) => (int) ($item['qty'] ?? 1));
        $hasCartItems = $cartItemCount > 0;
        $rentalDays = 0;

        if ($cartSuggestedStartDate && $cartSuggestedEndDate) {
            $rentalDays = \Carbon\Carbon::parse($cartSuggestedStartDate)->diffInDays(\Carbon\Carbon::parse($cartSuggestedEndDate)) + 1;
        }
    @endphp

    @push('head')
        <style>
            .cart-enter {
                animation: cart-enter 520ms ease-out both;
            }

            .cart-card-in {
                animation: cart-card-in 520ms ease-out both;
            }

            @keyframes cart-enter {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes cart-card-in {
                from {
                    opacity: 0;
                    transform: translateY(14px) scale(.98);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .cart-enter,
                .cart-card-in {
                    animation: none !important;
                }
            }
        </style>
    @endpush

    <div class="min-h-screen bg-[#0A0A0B] text-[#E8E8EC]">
        <div class="mx-auto max-w-7xl px-4 py-8 pb-24 sm:px-6 sm:py-10 lg:px-8">
            <header class="cart-enter rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">
                            {{ __('Keranjang Belanja') }}
                        </h1>
                        <p class="max-w-2xl text-sm leading-6 text-[#A0A0A8] sm:text-base">
                            {{ __('Cek alat, tanggal sewa, dan jumlah unit sebelum checkout.') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:justify-end">
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-4 py-2 text-sm font-semibold text-[#E8E8EC]">
                            <span class="h-2 w-2 rounded-full bg-[#D4A843]"></span>
                            {{ trans_choice(':count item|:count item', $cartUnitCount, ['count' => number_format($cartUnitCount, 0, ',', '.')]) }}
                        </div>

                        @if ($hasCartItems)
                            <form
                                action="{{ route('cart.clear') }}"
                                method="POST"
                                class="inline-flex"
                                onsubmit="return confirm('{{ __('Hapus semua item dari keranjang?') }}');"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-400/25 bg-rose-500/5 px-4 py-2.5 text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-400/30"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ __('Hapus Semua') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </header>

            @if (session('success') || session('error'))
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.75fr)] lg:gap-8">
                    <div>
                        @if (session('success'))
                            <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/8 px-4 py-3 text-sm font-medium text-emerald-200 cart-card-in">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="rounded-2xl border border-rose-400/20 bg-rose-500/8 px-4 py-3 text-sm font-medium text-rose-200 cart-card-in">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-rose-300"></span>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if (! $hasCartItems)
                <section class="mt-8">
                    <article class="cart-card-in rounded-3xl border border-white/10 bg-[#111113]/70 px-6 py-14 text-center sm:px-10 sm:py-16">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]">
                            <svg class="h-10 w-10 text-[#A0A0A8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h2 class="mt-6 text-2xl font-bold tracking-tight text-[#E8E8EC] sm:text-3xl">
                            {{ __('Keranjang masih kosong') }}
                        </h2>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-[#A0A0A8] sm:text-base">
                            {{ __('Pilih alat dari katalog untuk mulai menyusun kebutuhan sewa.') }}
                        </p>
                        <a
                            href="{{ route('catalog') }}"
                            class="mt-8 inline-flex items-center justify-center rounded-xl bg-[#D4A843] px-6 py-3.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40"
                        >
                            {{ __('Lihat Katalog') }}
                        </a>
                    </article>
                </section>
            @else
                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.75fr)] lg:items-start">
                    <section class="space-y-5">
                        @foreach ($cartItems as $item)
                            @php
                                $itemPrice = (int) ($item['price'] ?? 0);
                                $itemQty = (int) ($item['qty'] ?? 1);
                                $itemStock = max((int) ($item['stock'] ?? 99), 1);
                                $days = 1;

                                if (!empty($item['rental_start_date']) && !empty($item['rental_end_date'])) {
                                    $days = \Carbon\Carbon::parse($item['rental_start_date'])->diffInDays(\Carbon\Carbon::parse($item['rental_end_date'])) + 1;
                                }

                                $lineSubtotal = $itemPrice * $itemQty * $days;
                                $imageUrl = site_media_url($item['image_path'] ?? $item['image'] ?? null) ?: config('placeholders.equipment');
                                $dateRangeLabel = !empty($item['rental_start_date']) && !empty($item['rental_end_date'])
                                    ? \Carbon\Carbon::parse($item['rental_start_date'])->translatedFormat('d M Y') . ' — ' . \Carbon\Carbon::parse($item['rental_end_date'])->translatedFormat('d M Y')
                                    : __('Tanggal belum dipilih');
                            @endphp

                            <article
                                class="cart-card-in rounded-3xl border border-white/10 bg-[#111113]/70 p-5 shadow-[0_26px_70px_-52px_rgba(0,0,0,0.9)] sm:p-6"
                                style="animation-delay: {{ min($loop->index * 55, 220) }}ms"
                            >
                                <div class="flex flex-col gap-5 xl:flex-row xl:items-center">
                                    <div class="aspect-[4/3] w-full overflow-hidden rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B] xl:w-[140px] xl:shrink-0">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item['name'] }}"
                                            class="h-full w-full object-contain p-4"
                                            onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                        >
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-4">
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-1 text-xs font-semibold text-[#D4A843]">
                                                {{ $item['category'] ?? __('Umum') }}
                                            </span>
                                            <h3 class="text-xl font-bold leading-tight text-[#E8E8EC]">
                                                <a href="{{ route('product.show', $item['slug'] ?? '#') }}" class="transition hover:text-[#D4A843]">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 text-sm text-[#A0A0A8]">
                                            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5">
                                                <svg class="h-4 w-4 text-[#D4A843]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>{{ $dateRangeLabel }}</span>
                                            </div>
                                            <div class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5 text-[#E8E8EC]">
                                                {{ $days }} {{ __('hari') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full xl:w-[260px] xl:shrink-0">
                                        <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/80 p-4">
                                            <div class="space-y-1">
                                                <p class="text-2xl font-bold tracking-tight text-[#E8E8EC]">
                                                    Rp {{ number_format($lineSubtotal, 0, ',', '.') }}
                                                </p>
                                                <p class="text-xs leading-5 text-[#A0A0A8]">
                                                    {{ __('Rp :price × :qty unit × :days hari', [
                                                        'price' => number_format($itemPrice, 0, ',', '.'),
                                                        'qty' => $itemQty,
                                                        'days' => $days,
                                                    ]) }}
                                                </p>
                                            </div>

                                            <div class="mt-4 space-y-3">
                                                <div>
                                                    <p class="mb-2 text-xs font-semibold text-[#A0A0A8]">{{ __('Jumlah unit') }}</p>
                                                    <div class="flex items-center gap-2">
                                                        <form method="POST" action="{{ route('cart.decrement', $item['key']) }}" class="shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="submit"
                                                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/30 disabled:cursor-not-allowed disabled:opacity-40"
                                                                aria-label="{{ __('Kurangi jumlah') }}"
                                                                @disabled($itemQty <= 1)
                                                            >
                                                                <span class="text-lg font-bold">−</span>
                                                            </button>
                                                        </form>

                                                        <div class="flex h-11 min-w-[72px] items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#111113] px-3 text-base font-semibold text-[#E8E8EC]">
                                                            {{ $itemQty }}
                                                        </div>

                                                        <form method="POST" action="{{ route('cart.increment', $item['key']) }}" class="shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="submit"
                                                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#111113] text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/30 disabled:cursor-not-allowed disabled:opacity-40"
                                                                aria-label="{{ __('Tambah jumlah') }}"
                                                                @disabled($itemQty >= $itemStock)
                                                            >
                                                                <span class="text-lg font-bold">+</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <p class="mt-2 text-xs text-[#A0A0A8]">
                                                        {{ __('Maks. :stock unit tersedia.', ['stock' => number_format($itemStock, 0, ',', '.')]) }}
                                                    </p>
                                                </div>

                                                <form
                                                    method="POST"
                                                    action="{{ route('cart.remove', $item['key']) }}"
                                                    onsubmit="return confirm('{{ __('Hapus item ini dari keranjang?') }}');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-400/25 bg-rose-500/5 px-4 py-2.5 text-sm font-semibold text-rose-300 transition hover:border-rose-300/40 hover:bg-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-400/30"
                                                        aria-label="{{ __('Hapus item ini dari keranjang') }}"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        {{ __('Hapus') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <section class="cart-card-in lg:sticky lg:top-28" style="animation-delay: 120ms">
                        <article class="rounded-3xl border border-white/10 bg-[#111113]/70 p-6 shadow-[0_30px_80px_-48px_rgba(0,0,0,0.9)] sm:p-7">
                            <div class="space-y-1">
                                <h2 class="text-2xl font-bold tracking-tight text-[#E8E8EC]">
                                    {{ __('Ringkasan Biaya') }}
                                </h2>
                                <p class="text-sm text-[#A0A0A8]">
                                    {{ __('Total estimasi sebelum checkout.') }}
                                </p>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/75 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-[#A0A0A8]">{{ __('Masa sewa') }}</p>
                                            @if ($cartSuggestedStartDate && $cartSuggestedEndDate)
                                                <p class="mt-1 text-sm font-semibold leading-6 text-[#E8E8EC]">
                                                    {{ \Carbon\Carbon::parse($cartSuggestedStartDate)->translatedFormat('d M Y') }}
                                                    —
                                                    {{ \Carbon\Carbon::parse($cartSuggestedEndDate)->translatedFormat('d M Y') }}
                                                </p>
                                            @else
                                                <p class="mt-1 text-sm font-semibold leading-6 text-[#E8E8EC]">
                                                    {{ __('Ikuti tanggal pada item yang dipilih') }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="inline-flex shrink-0 items-center rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-1 text-xs font-semibold text-[#D4A843]">
                                            {{ $rentalDays > 0 ? $rentalDays . ' ' . __('hari') : __('Durasi menyesuaikan') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]/75 p-4">
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-[#A0A0A8]">{{ __('Subtotal') }}</span>
                                            <span class="font-semibold text-[#E8E8EC]">Rp {{ number_format($estimatedSubtotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-[#A0A0A8]">{{ __('PPN (11%)') }}</span>
                                            <span class="font-semibold text-[#E8E8EC]">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="border-t border-[#1A1A1E] pt-3">
                                            <div class="flex items-end justify-between gap-4">
                                                <span class="text-sm font-semibold text-[#E8E8EC]">{{ __('Total') }}</span>
                                                <span class="text-2xl font-bold tracking-tight text-[#D4A843]">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('checkout') }}"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-[#D4A843] px-5 py-3.5 text-sm font-semibold text-[#0A0A0B] transition hover:bg-[#e0ba5d] focus:outline-none focus:ring-2 focus:ring-[#D4A843]/40"
                                >
                                    {{ __('Lanjut Checkout') }}
                                </a>
                            </div>
                        </article>
                    </section>
                </div>
            @endif

            @if ($hasCartItems && $suggestedEquipments->isNotEmpty())
                <aside class="mt-12">
                    <div class="mb-5 space-y-1">
                        <h2 class="text-2xl font-bold tracking-tight text-[#E8E8EC]">
                            {{ __('Mungkin kamu butuh juga') }}
                        </h2>
                        <p class="text-sm text-[#A0A0A8]">
                            {{ __('Tambahan alat pendukung yang masih tersedia untuk jadwal sewa kamu.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($suggestedEquipments as $suggestion)
                            <article class="rounded-3xl border border-white/10 bg-[#111113]/60 p-4 transition hover:border-[#D4A843]/20 hover:bg-[#111113]/80">
                                <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-[#1A1A1E] bg-[#0A0A0B]">
                                    <img
                                        src="{{ site_media_url($suggestion->image_path ?? $suggestion->image ?? null) ?: config('placeholders.equipment') }}"
                                        alt="{{ $suggestion->name }}"
                                        class="h-full w-full object-contain p-3"
                                        onerror="this.onerror=null;this.src='{{ config('placeholders.equipment') }}';"
                                    >
                                </div>
                                <div class="mt-4 space-y-2">
                                    <span class="inline-flex items-center rounded-full border border-[#D4A843]/20 bg-[#D4A843]/10 px-3 py-1 text-[11px] font-semibold text-[#D4A843]">
                                        {{ $suggestion->category->name ?? __('Umum') }}
                                    </span>
                                    <h3 class="line-clamp-2 text-base font-semibold leading-6 text-[#E8E8EC]">
                                        <a href="{{ route('product.show', $suggestion->slug) }}" class="transition hover:text-[#D4A843]">
                                            {{ $suggestion->name }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-base font-semibold text-[#E8E8EC]">
                                            Rp {{ number_format($suggestion->price_per_day, 0, ',', '.') }}
                                            <span class="text-xs font-medium text-[#A0A0A8]">/ {{ __('hari') }}</span>
                                        </p>
                                        <a
                                            href="{{ route('product.show', $suggestion->slug) }}"
                                            class="inline-flex items-center justify-center rounded-xl border border-[#1A1A1E] bg-[#0A0A0B] px-3 py-2 text-xs font-semibold text-[#E8E8EC] transition hover:border-[#D4A843]/35 hover:text-[#D4A843]"
                                        >
                                            {{ __('Lihat') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>
    </div>
</x-app-layout>

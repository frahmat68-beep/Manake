@extends('layouts.app')

@section('title', $category->name ?? __('app.category.title'))

@section('content')
    @php
        $categoryName = $category->name ?? __('app.category.title');
        $categoryDescription = $category->description ?: setting('copy.category.subtitle', __('app.category.all_subtitle'));
        $totalLabel = setting('copy.category.total_label', __('ui.category.total_label'));
        $readyLabel = __('ui.category.ready_label');
        $emptyTitle = setting('copy.category.empty_title', __('ui.categories.empty_title'));
        $emptySubtitle = setting('copy.category.empty_subtitle', __('ui.categories.empty_subtitle'));
        $availabilityLineTemplate = __('ui.category.available_line');
        $availabilityNote = __('ui.category.availability_note');
        $categoryFallbackImage = 'https://images.unsplash.com/photo-1519183071298-a2962be96c68?auto=format&fit=crop&w=900&q=80';
        $items = collect($products ?? [])->values();
        $readyCount = $items->filter(fn ($product) => (($product->status ?? 'ready') === 'ready') && (int) ($product->stock ?? 0) > 0)->count();
    @endphp

    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <nav class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <a href="/" class="hover:text-blue-600">{{ __('app.breadcrumbs.home') }}</a>
                <span>/</span>
                <a href="{{ route('categories.index') }}" class="hover:text-blue-600">{{ __('app.breadcrumbs.category') }}</a>
                <span>/</span>
                <span class="font-semibold text-slate-700">{{ $categoryName }}</span>
            </nav>

            <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">{{ $categoryName }}</h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-600">{{ $categoryDescription }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge-status inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">
                        {{ $totalLabel }}: {{ $items->count() }}
                    </span>
                    <span class="badge-status badge-status-success inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">
                        {{ $readyLabel }}: {{ $readyCount }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-100">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            @if ($items->isEmpty())
                <div class="card rounded-2xl p-8 text-center shadow-sm">
                    <p class="text-base font-semibold text-slate-900">{{ $emptyTitle }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $emptySubtitle }}</p>
                    <a href="{{ route('catalog') }}" class="btn-secondary mt-5 inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition">
                        {{ __('app.actions.see_catalog') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $product)
                        @php
                            $name = data_get($product, 'name', __('app.product.generic'));
                            $slug = data_get($product, 'slug') ?? \Illuminate\Support\Str::slug($name);
                            $imagePath = data_get($product, 'image_path') ?? data_get($product, 'image');
                            $image = site_media_url($imagePath) ?: $categoryFallbackImage;
                            $price = (int) data_get($product, 'price_per_day', 0);
                            $statusValue = data_get($product, 'status') ?? ((int) data_get($product, 'stock', 0) > 0 ? 'ready' : 'unavailable');
                            $reservedUnits = (int) data_get($product, 'reserved_units', 0);
                            $availableUnits = (int) data_get($product, 'available_units', max((int) data_get($product, 'stock', 0) - $reservedUnits, 0));

                            $statusLabel = strtolower($statusValue) === 'ready'
                                ? __('app.status.ready')
                                : __('app.status.rented');
                            $statusClass = strtolower($statusValue) === 'ready'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-amber-100 text-amber-700';
                        @endphp

                        <article 
                            x-data="{}" 
                            @click="if (!$event.target.closest('button, a')) window.location.assign('{{ route('product.show', $slug) }}')"
                            class="card group flex h-full flex-col overflow-hidden rounded-2xl shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
                        >
                            <div class="relative flex h-56 items-center justify-center bg-slate-50 p-4 sm:h-60">
                                <img src="{{ $image }}" alt="{{ $name }}" class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ $categoryFallbackImage }}';" loading="lazy">
                                <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="min-h-[3.4rem] text-lg font-semibold leading-snug text-slate-900">{{ $name }}</h3>
                                <p class="mt-2 text-xs text-slate-500">{{ __('app.product.price_per_day') }}</p>
                                <p class="text-lg font-semibold text-slate-900">Rp {{ number_format($price, 0, ',', '.') }}</p>

                                <p class="mt-2 text-xs text-slate-500">
                                    {{ strtr($availabilityLineTemplate, [
                                        ':available' => (string) $availableUnits,
                                        ':stock' => (string) ((int) data_get($product, 'stock', 0)),
                                    ]) }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500">{{ $availabilityNote }}</p>

                                <a href="{{ route('product.show', $slug) }}" class="btn-primary mt-4 mt-auto inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition">
                                    {{ __('app.actions.view_detail') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

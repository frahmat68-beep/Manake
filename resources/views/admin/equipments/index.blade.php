@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('Kelola Alat'))
@section('page_title', __('Kelola Alat'))

@section('content')
    @php
        $search = $search ?? '';
        $status = $status ?? '';
        $activeCategorySlug = $activeCategory?->slug ?? '';
        $hasActiveFilter = $search !== '' || $status !== '' || $activeCategorySlug !== '';
        $statusFilters = [
            ['value' => '', 'label' => __('Semua Status')],
            ['value' => 'ready', 'label' => __('Siap')],
            ['value' => 'maintenance', 'label' => __('Perawatan')],
            ['value' => 'unavailable', 'label' => __('Tidak Tersedia')],
        ];
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="card rounded-2xl shadow-sm p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-blue-700">{{ __('Daftar Alat') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Kelola nama alat, harga, stok, dan status sewa.') }}</p>
                </div>
                <div class="flex w-full gap-2 sm:w-auto">
                    <a
                        href="{{ route('admin.equipments.create') }}"
                        class="btn-primary inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition sm:w-auto"
                    >
                        {{ __('+ Tambah Alat') }}
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.equipments.index') }}" class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="category" value="{{ $activeCategorySlug }}">
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:max-w-xl">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __('Cari alat...') }}"
                        class="input w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >
                    <button class="btn-primary rounded-xl px-4 py-2 text-sm font-semibold transition">{{ __('Cari') }}</button>
                    @if ($hasActiveFilter)
                        <a
                            href="{{ route('admin.equipments.index') }}"
                            class="btn-secondary inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                        >
                            {{ __('Atur Ulang Filter') }}
                        </a>
                    @endif
                </div>
                <div class="text-xs font-medium text-slate-500">
                    {{ __('Menampilkan') }} <span class="font-semibold text-slate-700">{{ $equipments->total() }}</span> {{ __('alat') }}
                </div>
            </form>

            <div class="mt-5 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('Filter Status') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($statusFilters as $filter)
                            @php
                                $isActiveStatus = $status === $filter['value'];
                            @endphp
                            <a
                                href="{{ route('admin.equipments.index', array_filter([
                                    'q' => $search,
                                    'status' => $filter['value'],
                                    'category' => $activeCategorySlug,
                                ], fn ($value) => $value !== '')) }}"
                                class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $isActiveStatus ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600' }}"
                            >
                                {{ $filter['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('Filter Kategori') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.equipments.index', array_filter([
                                'q' => $search,
                                'status' => $status,
                            ], fn ($value) => $value !== '')) }}"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $activeCategorySlug === '' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600' }}"
                        >
                            {{ __('Semua Kategori') }}
                        </a>
                        @foreach ($categories as $category)
                            <a
                                href="{{ route('admin.equipments.index', array_filter([
                                    'q' => $search,
                                    'status' => $status,
                                    'category' => $category->slug,
                                ], fn ($value) => $value !== '')) }}"
                                class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $activeCategorySlug === $category->slug ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600' }}"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="card rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">{{ __('Alat') }}</th>
                            <th class="px-5 py-3 text-left font-semibold">{{ __('Slug') }}</th>
                            <th class="px-5 py-3 text-right font-semibold">{{ __('Harga / Hari') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('Total Stok') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('Dipakai') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('Tersedia') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-center font-semibold">{{ __('Diperbarui') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($equipments as $item)
                            @php
                                $statusValue = $item->status ?? 'ready';
                                $statusLabel = $statusValue === 'ready' ? __('Siap') : ($statusValue === 'maintenance' ? __('Perawatan') : __('Tidak Tersedia'));
                                $statusClass = $statusValue === 'ready'
                                    ? 'status-chip-success'
                                    : ($statusValue === 'maintenance' ? 'status-chip-warning' : 'status-chip-danger');
                                $reservedUnits = (int) ($item->reserved_units ?? 0);
                                $availableUnits = (int) $item->available_units;
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4 align-top">
                                    <p class="max-w-[20rem] font-semibold leading-snug text-slate-900">{{ $item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->category?->name ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 align-top text-slate-600">
                                    <p class="max-w-[14rem] break-words">{{ $item->slug }}</p>
                                </td>
                                <td class="px-5 py-4 text-right align-top font-semibold whitespace-nowrap text-slate-900">{{ __('Rp') }} {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-center align-top font-semibold text-slate-900">{{ $item->stock }}</td>
                                <td class="px-5 py-4 text-center align-top font-semibold text-amber-600">{{ $reservedUnits }}</td>
                                <td class="px-5 py-4 text-center align-top">
                                    <span class="font-semibold {{ $availableUnits > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $availableUnits }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center align-top">
                                    <span class="status-chip {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center align-top text-slate-500 whitespace-nowrap">{{ $item->updated_at?->format('d M Y') }}</td>
                                <td class="px-5 py-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('admin.equipments.edit', $item->slug) }}"
                                            class="btn-secondary rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                                        >
                                            {{ __('Ubah') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.equipments.destroy', $item->slug) }}" data-confirm="{{ __('ui.dialog.delete_admin_item') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.actions.remove') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}" data-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100 transition">
                                                {{ __('Hapus') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-8 text-center text-sm text-slate-500">
                                    {{ __('Belum ada alat.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($equipments->hasPages())
            <div class="px-4">
                {{ $equipments->links() }}
            </div>
        @endif
    </div>
@endsection

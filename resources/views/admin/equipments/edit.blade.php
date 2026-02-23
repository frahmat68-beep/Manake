@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('Ubah Alat'))
@section('page_title', __('Ubah Alat'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-blue-700">{{ __('Ubah Alat') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Perbarui detail, slug, dan status alat.') }}</p>
                </div>
                <a href="{{ route('admin.equipments.index') }}" class="text-sm text-slate-600 hover:text-blue-600">{{ __('← Kembali ke Daftar') }}</a>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-widest text-slate-500">{{ __('Total Stok') }}</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $equipment->stock }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-widest text-slate-500">{{ __('Sedang Dipakai') }}</p>
                    <p class="mt-1 text-xl font-semibold text-amber-600">{{ $equipment->reserved_units }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-widest text-slate-500">{{ __('Unit Tersedia') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">{{ $equipment->available_units }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Kalender Pemakaian Alat') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Status alat tetap ready, tapi bentrok tanggal sewa akan ditolak saat checkout.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.equipments.edit', ['slug' => $equipment->slug, 'month' => $bookingCalendar['previous_month']]) }}"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600"
                        >
                            ←
                        </a>
                        <span class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $bookingCalendar['month_label'] }}</span>
                        <a
                            href="{{ route('admin.equipments.edit', ['slug' => $equipment->slug, 'month' => $bookingCalendar['next_month']]) }}"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600"
                        >
                            →
                        </a>
                    </div>
                </div>

                <div class="mt-3 -mx-1 overflow-x-auto px-1 pb-1 sm:mx-0 sm:overflow-visible sm:px-0">
                    <div class="min-w-[640px]">
                        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <span>{{ __('Sen') }}</span>
                            <span>{{ __('Sel') }}</span>
                            <span>{{ __('Rab') }}</span>
                            <span>{{ __('Kam') }}</span>
                            <span>{{ __('Jum') }}</span>
                            <span>{{ __('Sab') }}</span>
                            <span>{{ __('Min') }}</span>
                        </div>
                        <div class="mt-2 grid grid-cols-7 gap-2">
                            @foreach ($bookingCalendar['days'] as $day)
                                <div class="min-h-[68px] rounded-lg border px-2 py-2 {{ $day['in_month'] ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-100 text-slate-400' }}">
                                    <p class="text-xs font-semibold">{{ $day['day'] }}</p>
                                    @if ($day['booked_qty'] > 0)
                                        <p class="mt-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">
                                            {{ __('Disewa') }} {{ $day['booked_qty'] }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-[10px] text-slate-400">-</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse ($bookingCalendar['events'] as $event)
                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
                            <p class="font-semibold text-slate-800">{{ $event['order_number'] }} • {{ $event['customer'] }}</p>
                            <p class="mt-0.5">{{ \Carbon\Carbon::parse($event['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($event['end_date'])->translatedFormat('d M Y') }} • Qty {{ $event['qty'] }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">{{ __('Belum ada jadwal sewa pada bulan ini.') }}</p>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('admin.equipments.update', $equipment->slug) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Nama Alat') }}</label>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name', $equipment->name) }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Slug') }}</label>
                        <input
                            name="slug"
                            type="text"
                            value="{{ old('slug', $equipment->slug) }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Kategori') }}</label>
                        <select
                            name="category_id"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $equipment->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Harga / Hari') }}</label>
                        <input
                            name="price_per_day"
                            type="number"
                            value="{{ old('price_per_day', $equipment->price_per_day) }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Jumlah Barang (Total Stok)') }}</label>
                        <input
                            name="stock"
                            type="number"
                            min="0"
                            max="9999"
                            value="{{ old('stock', $equipment->stock) }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                        <p class="mt-1 text-xs text-slate-400">{{ __('Unit tersedia dihitung: total stok - unit yang sedang dipakai.') }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Status') }}</label>
                        <select
                            name="status"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                            <option value="ready" {{ old('status', $equipment->status) === 'ready' ? 'selected' : '' }}>{{ __('Siap') }}</option>
                            <option value="unavailable" {{ old('status', $equipment->status) === 'unavailable' ? 'selected' : '' }}>{{ __('Tidak Tersedia') }}</option>
                            <option value="maintenance" {{ old('status', $equipment->status) === 'maintenance' ? 'selected' : '' }}>{{ __('Perawatan') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('Upload Foto') }}</label>
                    <div class="mt-2 flex items-center justify-between gap-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ __('Tarik & lepas file') }}</p>
                            <p class="text-xs text-slate-500">{{ __('Format JPG/PNG/WEBP. Max 2MB.') }}</p>
                            @php
                                $imagePath = $equipment->image_path ?? $equipment->image;
                                $imageUrl = $imagePath ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath)) : null;
                            @endphp
                            @if ($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ __('Foto saat ini') }}" class="mt-3 h-24 w-24 rounded-xl object-contain border border-slate-200 bg-white p-1">
                            @endif
                        </div>
                        <label class="cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600 transition">
                            {{ __('Ganti File') }}
                            <input name="image" type="file" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('Spesifikasi') }}</label>
                    <textarea
                        name="specifications"
                        rows="5"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >{{ old('specifications', $equipment->specifications ?? $equipment->description) }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs text-slate-500">{{ __('Perubahan akan tersimpan dan kembali ke daftar.') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.equipments.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600 transition">{{ __('Batal') }}</a>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">{{ __('Simpan Perubahan') }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

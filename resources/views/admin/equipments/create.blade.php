@extends('layouts.admin', ['activePage' => 'equipments'])

@section('title', __('Tambah Alat'))
@section('page_title', __('Tambah Alat'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <section class="mk-card p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ __('Form Tambah Alat') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Isi data utama alat sebelum dipublikasikan.') }}</p>
                </div>
                <a href="{{ route('admin.equipments.index') }}" class="text-sm text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">{{ __('← Kembali ke Daftar') }}</a>
            </div>

            <form method="POST" action="{{ route('admin.equipments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Nama Alat') }}</label>
                        <input
                            name="name"
                            type="text"
                            placeholder="{{ __('Contoh: Sony A7 III') }}"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('name') }}"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Slug') }}</label>
                        <input
                            name="slug"
                            type="text"
                            placeholder="sony-a7-iii"
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('slug') }}"
                        >
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Slug dipakai untuk URL detail alat.') }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Kategori') }}</label>
                        <select
                            name="category_id"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Harga / Hari') }}</label>
                        <input
                            name="price_per_day"
                            type="number"
                            placeholder="350000"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('price_per_day') }}"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Jumlah Barang (Total Stok)') }}</label>
                        <input
                            name="stock"
                            type="number"
                            min="0"
                            max="9999"
                            placeholder="{{ __('Contoh: 5') }}"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('stock', 1) }}"
                        >
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Jumlah ini dipakai untuk hitung unit tersedia otomatis.') }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Status') }}</label>
                        <select
                            name="status"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                            <option value="ready" {{ old('status', 'ready') === 'ready' ? 'selected' : '' }}>{{ __('Siap') }}</option>
                            <option value="unavailable" {{ old('status') === 'unavailable' ? 'selected' : '' }}>{{ __('Tidak Tersedia') }}</option>
                            <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>{{ __('Perawatan') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Upload Foto') }}</label>
                    <div class="mt-2 flex items-center justify-between gap-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Tarik & lepas file') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Format JPG/PNG/WEBP. Max 2MB.') }}</p>
                        </div>
                        <label class="cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                            {{ __('Pilih File') }}
                            <input name="image" type="file" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Spesifikasi') }}</label>
                    <textarea
                        name="specifications"
                        rows="5"
                        placeholder="{{ __('Contoh:\n- Mount: Sony E\n- Focal length: 24-70mm\n- Isi box: body, cap, pouch') }}"
                        class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >{{ old('specifications') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Data akan langsung tampil di katalog pengguna.') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.equipments.index') }}" class="btn-secondary rounded-xl px-4 py-2 text-sm font-semibold transition">{{ __('Batal') }}</a>
                        <button type="submit" class="btn-primary rounded-xl px-5 py-2 text-sm font-semibold transition">{{ __('Simpan Alat') }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

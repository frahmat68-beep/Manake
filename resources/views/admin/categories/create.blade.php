@extends('layouts.admin', ['activePage' => 'categories'])

@section('title', __('Tambah Kategori'))
@section('page_title', __('Tambah Kategori'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Form Tambah Kategori') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Slug akan digunakan untuk URL kategori.') }}</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-600 hover:text-blue-600">{{ __('← Kembali ke Daftar') }}</a>
            </div>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Nama Kategori') }}</label>
                        <input
                            name="name"
                            type="text"
                            placeholder="{{ __('Contoh: Kamera') }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('name') }}"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Slug') }}</label>
                        <input
                            name="slug"
                            type="text"
                            placeholder="camera"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            value="{{ old('slug') }}"
                        >
                        <p class="mt-1 text-xs text-slate-400">{{ __('Kosongkan untuk generate otomatis.') }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('Deskripsi') }}</label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="{{ __('Deskripsi singkat kategori...') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs text-slate-500">{{ __('Kategori baru akan tampil di katalog.') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-blue-200 hover:text-blue-600 transition">{{ __('Batal') }}</a>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">{{ __('Simpan Kategori') }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

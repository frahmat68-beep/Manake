@extends('layouts.admin', ['activePage' => 'categories'])

@section('title', __('Ubah Kategori'))
@section('page_title', __('Ubah Kategori'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <section class="mk-card p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ __('Ubah Kategori') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Perbarui slug dan deskripsi kategori.') }}</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">{{ __('← Kembali ke Daftar') }}</a>
            </div>

            <form method="POST" action="{{ route('admin.categories.update', $category->slug) }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Nama Kategori') }}</label>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name', $category->name) }}"
                            required
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Slug') }}</label>
                        <input
                            name="slug"
                            type="text"
                            value="{{ old('slug', $category->slug) }}"
                            class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                        >
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ __('Kosongkan untuk generate otomatis.') }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Deskripsi') }}</label>
                    <textarea
                        name="description"
                        rows="4"
                        class="input mt-2 w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Perubahan akan tersimpan dan kembali ke daftar.') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="btn-secondary rounded-xl px-4 py-2 text-sm font-semibold transition">{{ __('Batal') }}</a>
                        <button type="submit" class="btn-primary rounded-xl px-5 py-2 text-sm font-semibold transition">{{ __('Simpan Perubahan') }}</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

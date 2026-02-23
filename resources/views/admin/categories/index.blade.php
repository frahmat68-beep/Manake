@extends('layouts.admin', ['activePage' => 'categories'])

@section('title', __('Kategori'))
@section('page_title', __('Kategori'))

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="card rounded-2xl shadow-sm p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-blue-700">{{ __('Daftar Kategori') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Kelola kategori dan slug untuk katalog.') }}</p>
                </div>
                <a
                    href="{{ route('admin.categories.create') }}"
                    class="btn-primary inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition"
                >
                    {{ __('+ Tambah Kategori') }}
                </a>
            </div>

            <form method="GET" action="{{ route('admin.categories.index') }}" class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('Cari kategori...') }}"
                        class="input w-full md:w-72 rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                    >
                    <button class="btn-primary rounded-xl px-4 py-2 text-sm font-semibold transition">{{ __('Cari') }}</button>
                </div>
            </form>
        </section>

        <section class="card rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-[680px] w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ __('Kategori') }}</th>
                            <th class="px-5 py-3">{{ __('Slug') }}</th>
                            <th class="px-5 py-3">{{ __('Peralatan') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $category->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $category->slug }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $category->equipments_count }} {{ __('alat') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('admin.categories.edit', $category->slug) }}"
                                            class="btn-secondary rounded-xl px-3 py-1.5 text-xs font-semibold transition"
                                        >
                                            {{ __('Ubah') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category->slug) }}" data-confirm="{{ __('ui.dialog.delete_admin_item') }}" data-confirm-title="{{ __('ui.dialog.title') }}" data-confirm-button="{{ __('ui.actions.remove') }}" data-cancel-button="{{ __('ui.dialog.cancel') }}" data-confirm-variant="danger">
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
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                    {{ __('Belum ada kategori.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($categories->hasPages())
            <div class="px-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection

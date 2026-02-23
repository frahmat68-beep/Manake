@extends('layouts.admin', ['activePage' => 'content'])

@section('title', __('Konten Lama & Media'))
@section('page_title', __('Konten Lama & Media'))

@php
    $groupLabels = [
        'home' => __('Beranda'),
        'footer' => __('Footer'),
        'contact' => __('Kontak'),
    ];

    $groupedSchema = collect($schema ?? [])->groupBy('group');
@endphp

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.content.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @foreach ($groupedSchema as $group => $fields)
                <section class="card rounded-2xl p-6 shadow-sm">
                    <div class="mb-4 flex flex-col gap-1">
                        <h2 class="text-lg font-semibold text-slate-900">{{ $groupLabels[$group] ?? ucfirst($group) }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Ini halaman lama untuk konten campuran teks + media.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        @foreach ($fields as $field => $meta)
                            @php
                                $settingKey = $meta['key'];
                                $fieldType = $meta['type'];
                                $value = old($field, $settings[$settingKey] ?? null);
                                $altValue = old($field . '_alt', $settings[$settingKey . '_alt'] ?? null);
                                $imageUrl = $fieldType === 'image' ? site_media_url($value) : null;
                            @endphp

                            <div class="{{ $fieldType === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label for="{{ $field }}" class="text-xs font-semibold text-slate-500">{{ $meta['label'] }}</label>

                                @if ($fieldType === 'textarea')
                                    <textarea
                                        id="{{ $field }}"
                                        name="{{ $field }}"
                                        rows="4"
                                        class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                    >{{ $value }}</textarea>
                                @elseif ($fieldType === 'image')
                                    <div class="mt-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $altValue ?: $meta['label'] }}" class="h-32 w-full rounded-xl border border-slate-200 object-cover bg-white">
                                        @else
                                            <div class="flex h-32 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-400">
                                                {{ __('Belum ada gambar') }}
                                            </div>
                                        @endif

                                        <div class="mt-3 space-y-3">
                                            <input
                                                id="{{ $field }}"
                                                name="{{ $field }}"
                                                type="file"
                                                accept="image/*"
                                                class="input w-full rounded-xl px-3 py-2 text-xs text-slate-600"
                                            >
                                            <input
                                                name="{{ $field }}_alt"
                                                type="text"
                                                value="{{ $altValue }}"
                                                placeholder="{{ __('Alt text gambar (opsional)') }}"
                                                class="input w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                            >
                                        </div>
                                    </div>
                                @else
                                    <input
                                        id="{{ $field }}"
                                        name="{{ $field }}"
                                        type="text"
                                        value="{{ $value }}"
                                        class="input mt-2 w-full rounded-xl px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                                    >
                                @endif

                                @error($field)
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">{{ __('Untuk mengedit teks situs pengguna, gunakan menu baru:') }} <strong>{{ __('Teks Website') }}</strong>.</p>
                <button type="submit" class="btn-primary inline-flex items-center justify-center rounded-xl px-6 py-2.5 text-sm font-semibold transition">
                    {{ __('Simpan Konten') }}
                </button>
            </div>
        </form>
    </div>
@endsection

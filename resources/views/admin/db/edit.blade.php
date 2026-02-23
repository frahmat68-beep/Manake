@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('Ubah Data'))
@section('page_title', __('Data Database'))

@section('content')
    @php
        $isEditable = fn ($field) => ! in_array($field, [$primaryKey, 'created_at', 'updated_at'], true);
        $fieldType = function (string $type) {
            $type = strtolower($type);
            if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                return 'number';
            }
            if (str_contains($type, 'date') && ! str_contains($type, 'datetime') && ! str_contains($type, 'timestamp')) {
                return 'date';
            }
            if (str_contains($type, 'datetime') || str_contains($type, 'timestamp')) {
                return 'datetime-local';
            }
            if (str_contains($type, 'time')) {
                return 'time';
            }
            if (str_contains($type, 'text') || str_contains($type, 'json')) {
                return 'textarea';
            }
            return 'text';
        };
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('Ubah Data') }}</p>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $table }} #{{ data_get($record, $primaryKey) }}</h2>
            </div>
            <a href="{{ route('admin.db.show', [$table, data_get($record, $primaryKey)]) }}" class="text-sm text-slate-500 hover:text-blue-600 dark:text-slate-300">{{ __('← Batal Ubah') }}</a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.db.update', [$table, data_get($record, $primaryKey)]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4 dark:border-slate-800 dark:bg-slate-900">
                @foreach ($columns as $column)
                    @php
                        $field = $column['Field'];
                        $type = $column['Type'];
                        $inputType = $fieldType($type);
                        $value = data_get($record, $field);
                        if ($inputType === 'datetime-local' && $value) {
                            $value = \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i');
                        }
                    @endphp

                    @if ($isEditable($field))
                        <div>
                            <label class="text-xs font-semibold text-slate-500 dark:text-slate-300">{{ $field }}</label>

                            @if ($inputType === 'textarea')
                                <textarea
                                    name="{{ $field }}"
                                    rows="3"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                >{{ old($field, $value) }}</textarea>
                            @else
                                <input
                                    type="{{ $inputType }}"
                                    name="{{ $field }}"
                                    value="{{ old($field, $value) }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                >
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <label class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="confirm_update" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500" required>
                <span>{{ __('Saya memahami perubahan ini akan langsung mengubah database.') }}</span>
            </label>

            <button class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                {{ __('Simpan Data') }}
            </button>
        </form>
    </div>
@endsection

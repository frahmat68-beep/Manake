@extends('layouts.admin', ['activePage' => 'db'])

@section('title', __('ui.admin_db.edit.title'))
@section('page_title', __('ui.admin_db.page_title'))

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

        $dbCopy = __('ui.admin_db');

        if (! is_array($dbCopy)) {
            $dbCopy = [
                'page_title' => 'Database Data',
                'edit' => [
                    'title' => 'Edit Data',
                    'kicker' => 'Edit Data',
                    'cancel' => '← Cancel Edit',
                    'confirm' => 'I understand this change will directly update the database.',
                    'save' => 'Save Data',
                ],
            ];
        }
    @endphp

@push('head')

<style>
    .admin-db-page {
        color: var(--admin-text);
    }

    .admin-db-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-db-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-db-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-db-title {
        color: var(--admin-text);
    }

    .admin-db-muted {
        color: var(--admin-muted);
    }

    .admin-db-subtle {
        color: var(--admin-subtle);
    }

    .admin-db-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-db-input {
        width: 100%;
        min-height: 2.9rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0.7rem 0.9rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-db-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-db-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-db-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr td {
        background: #FFFFFF !important;
        color: #111827 !important;
        border-bottom-color: #E5E7EB !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover td {
        background: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr td {
        background: #111113 !important;
        color: #E8E8EC !important;
        border-bottom-color: #1A1A1E !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table tbody tr:hover td {
        background: #151519 !important;
        color: #E8E8EC !important;
    }

    .admin-db-warning {
        border: 1px solid rgba(245, 158, 11, 0.25);
        background: rgba(245, 158, 11, 0.1);
        color: #B45309;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-warning {
        background: rgba(217, 173, 63, 0.12) !important;
        color: #D9AD3F !important;
        border-color: rgba(217, 173, 63, 0.25) !important;
    }

    .admin-db-table-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1rem;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .admin-db-table-card:hover {
        transform: translateY(-1px);
        border-color: var(--admin-accent);
        box-shadow: 0 16px 35px -28px rgba(0,0,0,0.35);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-db-table-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-db-table-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
    }
</style>

@endpush

    <div class="admin-db-page mx-auto max-w-5xl space-y-5 sm:space-y-6">
        <div class="admin-db-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-db-kicker">{{ $dbCopy['edit']['kicker'] }}</p>
                    <h2 class="admin-db-title mt-2 text-2xl font-black">
                        {{ $table }} #{{ data_get($record, $primaryKey) }}
                    </h2>
                </div>
                <a href="{{ route('admin.db.show', [$table, data_get($record, $primaryKey)]) }}" class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                    {{ $dbCopy['edit']['cancel'] }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.db.update', [$table, data_get($record, $primaryKey)]) }}" class="space-y-5 sm:space-y-6">
            @csrf
            @method('PUT')

            <div class="admin-db-card space-y-4 p-5 sm:p-6">
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
                            <label class="admin-db-kicker">{{ $field }}</label>

                            @if ($inputType === 'textarea')
                                <textarea
                                    name="{{ $field }}"
                                    rows="3"
                                    class="admin-db-input mt-2 min-h-[8rem] resize-y"
                                >{{ old($field, $value) }}</textarea>
                            @else
                                <input
                                    type="{{ $inputType }}"
                                    name="{{ $field }}"
                                    value="{{ old($field, $value) }}"
                                    class="admin-db-input mt-2"
                                >
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <label class="admin-db-card flex items-start gap-3 p-4 text-sm cursor-pointer">
                <input type="checkbox" name="confirm_update" class="mt-1 h-4 w-4 rounded border-[var(--admin-border)] accent-[var(--admin-accent)]" required>
                <span class="admin-db-title font-semibold">{{ $dbCopy['edit']['confirm'] }}</span>
            </label>

            <button class="admin-accent-bg inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                {{ $dbCopy['edit']['save'] }}
            </button>
        </form>
    </div>
@endsection

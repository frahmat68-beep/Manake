@extends('layouts.admin', ['activePage' => 'users'])

@section('title', __('ui.admin_users.title'))
@section('page_title', __('ui.admin_users.page_title'))

@push('head')
<style>
    .admin-users-page {
        color: var(--admin-text);
    }

    .admin-users-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-users-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-users-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-users-title {
        color: var(--admin-text);
    }

    .admin-users-muted {
        color: var(--admin-muted);
    }

    .admin-users-subtle {
        color: var(--admin-subtle);
    }

    .admin-users-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-users-input {
        width: 100%;
        min-height: 3.05rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.95rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-users-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-users-table thead {
        background: var(--admin-surface-raised);
        color: var(--admin-muted);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table thead {
        background: #F8FAFC !important;
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table thead {
        background: #0A0A0B !important;
        color: #A0A0A8 !important;
    }

    .admin-users-table tbody tr {
        background: transparent !important;
        color: var(--admin-text) !important;
        border-bottom: 1px solid var(--admin-border);
        transition: background-color 160ms ease, color 160ms ease;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr td {
        background: #FFFFFF !important;
        color: #111827 !important;
        border-bottom-color: #E5E7EB !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover,
    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover td {
        background: #F8FAFC !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover .admin-users-title {
        color: #111827 !important;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover .admin-users-muted {
        color: #4B5563 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr td {
        background: #111113 !important;
        color: #E8E8EC !important;
        border-bottom-color: #1A1A1E !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover,
    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover td {
        background: #151519 !important;
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover .admin-users-title {
        color: #E8E8EC !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-users-table tbody tr:hover .admin-users-muted {
        color: #A0A0A8 !important;
    }

    .admin-users-table tbody tr:last-child {
        border-bottom: 0 !important;
    }
</style>
@endpush

@section('content')
    @php
        $usersCopy = __('ui.admin_users');

        if (! is_array($usersCopy)) {
            $usersCopy = [
                'title' => 'Users',
                'page_title' => 'Users',
                'kicker' => 'Users',
                'heading' => 'User Data',
                'subtitle' => 'Admins can only view user profiles and send password reset links. Original passwords remain hashed.',
                'filters' => [
                    'search_placeholder' => 'Search user name/email...',
                    'search' => 'Search',
                    'reset' => 'Reset',
                ],
                'table' => [
                    'title' => 'User List',
                    'subtitle' => 'Review user verification status and profile completion.',
                    'user' => 'User',
                    'email_status' => 'Email Status',
                    'phone_status' => 'Phone Status',
                    'profile' => 'Profile',
                    'action' => 'Action',
                    'details' => 'Details',
                    'empty' => 'No users found.',
                ],
                'status' => [
                    'verified' => 'Verified',
                    'unverified' => 'Unverified',
                    'complete' => 'Complete',
                    'incomplete' => 'Incomplete',
                ],
            ];
        }
    @endphp

    <div class="admin-users-page mx-auto max-w-7xl space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <section class="admin-users-card p-5 sm:p-6">
            <p class="admin-users-kicker">{{ $usersCopy['kicker'] }}</p>
            <h2 class="admin-users-title mt-2 text-2xl font-black">
                {{ $usersCopy['heading'] }}
            </h2>
            <p class="admin-users-muted mt-1 text-sm">
                {{ $usersCopy['subtitle'] }}
            </p>

            <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 flex flex-col gap-3 md:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ $usersCopy['filters']['search_placeholder'] }}"
                    class="admin-users-input"
                >
                <button class="admin-accent-bg inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-5 text-sm font-bold transition">
                    {{ $usersCopy['filters']['search'] }}
                </button>

                @if (!empty($search))
                    <a href="{{ route('admin.users.index') }}" class="admin-secondary-button inline-flex min-h-[3.05rem] items-center justify-center rounded-xl px-4 text-sm font-semibold transition">
                        {{ $usersCopy['filters']['reset'] }}
                    </a>
                @endif
            </form>
        </section>

        <section class="admin-users-card overflow-hidden p-0">
            <div class="flex flex-col gap-1 border-b px-5 py-4 admin-border">
                <h3 class="admin-users-title text-lg font-black">
                    {{ $usersCopy['table']['title'] }}
                </h3>
                <p class="admin-users-muted text-sm">
                    {{ $usersCopy['table']['subtitle'] }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-users-table w-full min-w-[900px] table-fixed text-sm">
                    <colgroup>
                        <col class="w-[34%]">
                        <col class="w-[17%]">
                        <col class="w-[17%]">
                        <col class="w-[17%]">
                        <col class="w-[15%]">
                    </colgroup>
                    <thead>
                        <tr class="admin-users-table-thead">
                            <th class="px-5 py-4 text-[11px] font-black uppercase tracking-[0.16em]">{{ $usersCopy['table']['user'] }}</th>
                            <th class="px-5 py-4 text-[11px] font-black uppercase tracking-[0.16em]">{{ $usersCopy['table']['email_status'] }}</th>
                            <th class="px-5 py-4 text-[11px] font-black uppercase tracking-[0.16em]">{{ $usersCopy['table']['phone_status'] }}</th>
                            <th class="px-5 py-4 text-[11px] font-black uppercase tracking-[0.16em]">{{ $usersCopy['table']['profile'] }}</th>
                            <th class="px-5 py-4 text-right text-[11px] font-black uppercase tracking-[0.16em]">{{ $usersCopy['table']['action'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-5 py-4 align-middle">
                                    <p class="truncate font-bold admin-users-title" title="{{ $user->name }}">
                                        {{ $user->name }}
                                    </p>
                                    <p class="mt-0.5 truncate text-sm admin-users-muted" title="{{ $user->email }}">
                                        {{ $user->email }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if ($user->email_verified_at)
                                        <span class="status-chip status-chip-success">{{ $usersCopy['status']['verified'] }}</span>
                                    @else
                                        <span class="status-chip status-chip-warning">{{ $usersCopy['status']['unverified'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if ($user->profile?->phone_verified_at)
                                        <span class="status-chip status-chip-success">{{ $usersCopy['status']['verified'] }}</span>
                                    @else
                                        <span class="status-chip status-chip-warning">{{ $usersCopy['status']['unverified'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    @if ($user->profileIsComplete())
                                        <span class="status-chip status-chip-info">{{ $usersCopy['status']['complete'] }}</span>
                                    @else
                                        <span class="status-chip status-chip-muted">{{ $usersCopy['status']['incomplete'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right align-middle">
                                    <a href="{{ route('admin.users.show', $user) }}" class="admin-secondary-button inline-flex min-h-9 items-center justify-center rounded-lg px-3 text-xs font-bold transition">
                                        {{ $usersCopy['table']['details'] }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm admin-users-muted">
                                    {{ $usersCopy['table']['empty'] }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($users->hasPages())
            <div class="px-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

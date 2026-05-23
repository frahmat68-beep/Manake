@extends('layouts.admin', ['activePage' => 'users'])

@section('title', __('Pengguna'))
@section('page_title', __('Pengguna'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="mk-card border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <section class="mk-card p-6">
            <h2 class="text-lg font-semibold text-blue-700 dark:text-blue-400">{{ __('Data Pengguna') }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Admin hanya bisa melihat profil pengguna dan mengirim tautan atur ulang kata sandi. Kata sandi asli tetap dalam bentuk hash.') }}</p>

            <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 flex flex-col gap-3 md:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="{{ __('Cari nama / email pengguna...') }}"
                    class="input w-full rounded-xl px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                >
                <button class="btn-primary rounded-xl px-4 py-2 text-sm font-semibold transition">{{ __('Cari') }}</button>
            </form>
        </section>

        <section class="mk-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3">{{ __('Pengguna') }}</th>
                            <th class="px-5 py-3">{{ __('Status Email') }}</th>
                            <th class="px-5 py-3">{{ __('Status Telepon') }}</th>
                            <th class="px-5 py-3">{{ __('Profil') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/60">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-50">{{ $user->name }}</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->email_verified_at)
                                        <span class="status-chip status-chip-success">{{ __('Terverifikasi') }}</span>
                                    @else
                                        <span class="status-chip status-chip-warning">{{ __('Belum Verifikasi') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->profile?->phone_verified_at)
                                        <span class="status-chip status-chip-success">{{ __('Terverifikasi') }}</span>
                                    @else
                                        <span class="status-chip status-chip-warning">{{ __('Belum Verifikasi') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->profileIsComplete())
                                        <span class="status-chip status-chip-info">{{ __('Lengkap') }}</span>
                                    @else
                                        <span class="status-chip status-chip-muted">{{ __('Belum Lengkap') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:text-slate-300 dark:hover:border-blue-500/40 dark:hover:text-blue-300">
                                        {{ __('Detail') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada pengguna.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $users->links() }}
    </div>
@endsection

@extends('layouts.admin', ['activePage' => 'users'])

@section('title', 'Pengguna')
@section('page_title', 'Pengguna')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-blue-700">Data Pengguna</h2>
            <p class="text-xs text-slate-500">Admin hanya bisa melihat profil pengguna dan mengirim tautan atur ulang kata sandi. Kata sandi asli tetap dalam bentuk hash.</p>

            <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 flex flex-col gap-3 md:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama / email pengguna..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                >
                <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Cari</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Telepon</th>
                            <th class="px-5 py-3">Profil</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="text-sm text-slate-600">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->email_verified_at)
                                        <span class="status-chip status-chip-success">Terverifikasi</span>
                                    @else
                                        <span class="status-chip status-chip-warning">Belum Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->profile?->phone_verified_at)
                                        <span class="status-chip status-chip-success">Terverifikasi</span>
                                    @else
                                        <span class="status-chip status-chip-warning">Belum Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->profileIsComplete())
                                        <span class="status-chip status-chip-info">Lengkap</span>
                                    @else
                                        <span class="status-chip status-chip-muted">Belum Lengkap</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="inline-flex rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $users->links() }}
    </div>
@endsection

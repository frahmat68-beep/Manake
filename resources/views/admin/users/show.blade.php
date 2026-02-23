@extends('layouts.admin', ['activePage' => 'users'])

@section('title', __('Detail Pengguna'))
@section('page_title', __('Detail Pengguna'))

@section('content')
    @php
        $profile = $user->profile;
        $addressText = $profile?->address_text ?? '-';
        $formatStatus = function (bool $ok, ?string $okText = null, ?string $noText = null): string {
            $okLabel = $okText ?? __('Terverifikasi');
            $noLabel = $noText ?? __('Belum Verifikasi');

            return $ok
                ? '<span class="status-chip status-chip-success">' . $okLabel . '</span>'
                : '<span class="status-chip status-chip-warning">' . $noLabel . '</span>';
        };
        $formatProfileStatus = fn (bool $ok) => $ok
            ? '<span class="status-chip status-chip-info">' . __('Lengkap') . '</span>'
            : '<span class="status-chip status-chip-muted">' . __('Belum Lengkap') . '</span>';
        $formatOrderStatus = fn (?string $status) => match ((string) $status) {
            'menunggu_pembayaran' => __('Menunggu Pembayaran'),
            'diproses' => __('Diproses'),
            'lunas' => __('Siap Diambil'),
            'barang_diambil' => __('Barang Diambil'),
            'barang_kembali' => __('Barang Dikembalikan'),
            'barang_rusak' => __('Barang Rusak'),
            'barang_hilang' => __('Barang Hilang'),
            'overdue_denda' => __('Denda Overdue'),
            'selesai' => __('Selesai'),
            'dibatalkan' => __('Dibatalkan'),
            'refund' => __('Pengembalian Dana'),
            default => strtoupper((string) $status),
        };
    @endphp

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

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Pengguna') }}</p>
                <h2 class="text-2xl font-semibold text-blue-700">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600">{{ __('← Kembali ke Pengguna') }}</a>
        </div>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr,0.8fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-blue-700">{{ __('Profil Pengguna') }}</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <p><span class="font-semibold text-slate-800">{{ __('Nama Lengkap:') }}</span> {{ $profile?->full_name ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('NIK:') }}</span> {{ $profile?->nik ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Tanggal Lahir:') }}</span> {{ optional($profile?->date_of_birth)->format('d M Y') ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Gender:') }}</span> {{ $profile?->gender ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('No. Telepon:') }}</span> {{ $profile?->phone ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Role:') }}</span> {{ strtoupper($user->role ?? __('pengguna')) }}</p>
                    <p class="sm:col-span-2"><span class="font-semibold text-slate-800">{{ __('Alamat:') }}</span> {{ $addressText }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Google Maps:') }}</span> {{ $profile?->maps_url ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Kode Pos:') }}</span> {{ $profile?->postal_code ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Kontak Darurat:') }}</span> {{ $profile?->emergency_name ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Hubungan Darurat:') }}</span> {{ $profile?->emergency_relation ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('No. Darurat:') }}</span> {{ $profile?->emergency_phone ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Status Email:') }}</span> {!! $formatStatus((bool) $user->email_verified_at) !!}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Status Telepon:') }}</span> {!! $formatStatus((bool) ($profile?->phone_verified_at), __('Terverifikasi'), __('Belum Verifikasi')) !!}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Status Profil:') }}</span> {!! $formatProfileStatus($user->profileIsComplete()) !!}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Tanggal Lengkap:') }}</span> {{ optional($profile?->completed_at)->format('d M Y H:i') ?? '-' }}</p>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-slate-900">{{ __('Pesanan Terbaru') }}</h4>
                    <div class="mt-3 space-y-2">
                        @forelse ($user->orders as $order)
                            @php
                                $paymentLabel = match ((string) ($order->status_pembayaran ?? 'pending')) {
                                    'paid' => __('LUNAS'),
                                    'failed' => __('GAGAL'),
                                    default => __('MENUNGGU'),
                                };
                            @endphp
                            <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3 text-sm">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs text-slate-500">{{ $paymentLabel }} • {{ $formatOrderStatus($order->status_pesanan) }}</p>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">{{ __('Lihat') }}</a>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('Belum ada pesanan.') }}</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <aside class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Aksi Keamanan') }}</h3>
                <p class="text-sm text-slate-500">
                    {{ __('Kata sandi pengguna tidak ditampilkan ke admin. Sistem hanya menyimpan hash kata sandi.') }}
                </p>

                <form method="POST" action="{{ route('admin.users.set-password', $user) }}" class="space-y-3 rounded-xl border border-slate-200 p-4">
                    @csrf
                    <p class="text-sm font-semibold text-slate-900">{{ __('Atur Kata Sandi Baru') }}</p>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Kata Sandi Baru') }}</label>
                        <input
                            type="password"
                            name="new_password"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            required
                        >
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-2 focus:ring-blue-500/30 focus:outline-none"
                            required
                        >
                    </div>
                    <button class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        {{ __('Simpan Kata Sandi Baru') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <button class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                        {{ __('Kirim Tautan Atur Ulang Kata Sandi ke Email') }}
                    </button>
                </form>
            </aside>
        </section>
    </div>
@endsection

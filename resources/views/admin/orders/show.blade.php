@extends('layouts.admin', ['activePage' => 'orders'])

@section('title', __('Detail Pesanan'))
@section('page_title', __('Detail Pesanan'))

@section('content')
    @php
        $formatIdr = fn ($value) => __('Rp') . ' ' . number_format((int) $value, 0, ',', '.');

        $statusLabel = function (?string $status) {
            return match ($status) {
                'menunggu_pembayaran' => __('Menunggu Pembayaran'),
                'diproses' => __('Diproses'),
                'lunas' => __('Siap Diambil'),
                'barang_diambil' => __('Barang Diambil'),
                'barang_kembali' => __('Barang Dikembalikan'),
                'barang_rusak' => __('Barang Rusak'),
                'selesai' => __('Selesai'),
                'dibatalkan' => __('Dibatalkan'),
                'refund' => __('Pengembalian Dana'),
                default => strtoupper((string) $status),
            };
        };
        $paymentLabel = function (?string $status) {
            return match ((string) $status) {
                'paid' => __('Lunas'),
                'failed' => __('Gagal'),
                'expired' => __('Kedaluwarsa'),
                default => __('Menunggu'),
            };
        };
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="mk-card-soft px-4 py-3 text-sm text-emerald-500">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mk-card-soft px-4 py-3 text-sm text-rose-500">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mk-card flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between p-6">
            <div>
                <p class="manake-kicker">{{ __('Pesanan') }}</p>
                <h2 class="manake-display mt-2 text-3xl font-black text-[#E8E8EC]">{{ $order->order_number ?? ('ORD-' . $order->id) }}</h2>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary w-full sm:w-auto">{{ __('← Kembali ke Daftar Pesanan') }}</a>
        </div>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr,0.8fr]">
            <article class="mk-card p-6">
                <h3 class="manake-heading text-lg font-black text-[#E8E8EC]">{{ __('Item Pesanan') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($order->items as $item)
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item->equipment?->name ?? __('Alat') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Qty') }} {{ $item->qty }} x {{ $formatIdr($item->price) }}</p>
                                </div>
                                <p class="font-semibold text-slate-800">{{ $formatIdr($item->subtotal) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('Tidak ada item di pesanan ini.') }}</p>
                    @endforelse
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">{{ __('Pembayaran:') }}</span> {{ $paymentLabel($order->status_pembayaran) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">{{ __('Status Sewa:') }}</span> {{ $statusLabel($order->status_pesanan) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">{{ __('Subtotal:') }}</span> {{ $formatIdr($order->total_amount) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">{{ __('Biaya Tambahan:') }}</span> {{ $formatIdr($order->additional_fee ?? 0) }}</p>
                        <p class="mt-1"><span class="font-semibold text-slate-800">{{ __('Total Akhir:') }}</span> {{ $formatIdr($order->grand_total) }}</p>
                    </div>
                </div>
            </article>

            <!-- Data Penyewa -->
            <article class="mk-card p-6 mt-6">
                <h3 class="manake-heading text-lg font-black text-[#E8E8EC]">{{ __('Data Penyewa') }}</h3>
                @php
                    $u = $order->user;
                    $p = $u?->profile;
                @endphp
                @if ($u && $p)
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm text-[#A0A0A8]">
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->full_name ?? $u->name }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">NIK (Masked)</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->masked_nik ?? '-' }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Email</span>
                            <span class="text-[#E8E8EC] font-semibold">
                                {{ $u->email }} 
                                <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded {{ $u->email_verified_at ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                    {{ $u->email_verified_at ? 'Terverifikasi' : 'Belum Verifikasi' }}
                                </span>
                            </span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">No. Telepon</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->phone ?? '-' }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Lahir</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ optional($p->date_of_birth)->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->gender === 'male' ? 'Laki-laki' : ($p->gender === 'female' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5 sm:col-span-2">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->address_text ?? '-' }}</span>
                        </div>
                        @if ($p->maps_url)
                            <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5 sm:col-span-2">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Google Maps Link</span>
                                <a href="{{ $p->maps_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-400 font-semibold hover:underline">
                                    Buka Lokasi di Maps
                                </a>
                            </div>
                        @endif
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kontak Darurat</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->emergency_name ?? '-' }} ({{ $p->emergency_relation ?? '-' }})</span>
                        </div>
                        <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">No. Kontak Darurat</span>
                            <span class="text-[#E8E8EC] font-semibold">{{ $p->emergency_phone ?? '-' }}</span>
                        </div>
                        @if ($p->alternative_phone)
                            <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">No. Telepon Alternatif</span>
                                <span class="text-[#E8E8EC] font-semibold">{{ $p->alternative_phone }}</span>
                            </div>
                        @endif
                        @if ($p->instagram_handle)
                            <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Instagram Username</span>
                                <span class="text-[#E8E8EC] font-semibold">{{ $p->instagram_handle }}</span>
                            </div>
                        @endif
                        @if ($p->organization_name)
                            <div class="rounded-xl border border-slate-200/40 p-3 bg-white/5 sm:col-span-2">
                                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Instansi/Organisasi</span>
                                <span class="text-[#E8E8EC] font-semibold">{{ $p->organization_name }} ({{ strtoupper($p->organization_type ?? '-') }})</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500 mt-2">Data penyewa tidak ditemukan atau profil belum selesai diisi.</p>
                @endif
            </article>

            <aside class="mk-card p-6 space-y-4">
                <h3 class="manake-heading text-lg font-black text-[#E8E8EC]">{{ __('Kontrol Status') }}</h3>
                <div class="space-y-2 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-800">{{ __('Pengguna:') }}</span> {{ $order->user?->name ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Email:') }}</span> {{ $order->user?->email ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-800">{{ __('Periode:') }}</span> {{ optional($order->rental_start_date)->format('d M Y') }} - {{ optional($order->rental_end_date)->format('d M Y') }}</p>
                    @if ($order->picked_up_at)
                        <p><span class="font-semibold text-slate-800">{{ __('Diambil:') }}</span> {{ $order->picked_up_at->format('d M Y H:i') }}</p>
                    @endif
                    @if ($order->returned_at)
                        <p><span class="font-semibold text-slate-800">{{ __('Dikembalikan:') }}</span> {{ $order->returned_at->format('d M Y H:i') }}</p>
                    @endif
                    @if ($order->damaged_at)
                        <p><span class="font-semibold text-slate-800">{{ __('Rusak Dilaporkan:') }}</span> {{ $order->damaged_at->format('d M Y H:i') }}</p>
                    @endif
                </div>

                <!-- Operational Checklist -->
                <div class="rounded-2xl border border-slate-200/40 p-4 bg-white/5 space-y-2">
                    <h4 class="text-sm font-semibold text-[#E8E8EC]">Checklist Validasi Pengambilan</h4>
                    @php
                        $u = $order->user;
                        $p = $u?->profile;
                        
                        $isEmailVerified = (bool) ($u?->email_verified_at);
                        $isConsentAccepted = (bool) optional($p)->rental_consent_accepted_at;
                        $isPhoneAvailable = (bool) ($p?->phone);
                        $isProfileComplete = (bool) ($u?->hasCompleteRentalProfile());
                        $isEmergencyAvailable = (bool) ($p?->emergency_name && $p?->emergency_phone);
                        $isAddressAvailable = (bool) ($p?->address_line || $p?->address);
                        $isPaymentPaid = $order->status_pembayaran === 'paid';
                        $isReadyForPickup = in_array($order->status_pesanan, ['lunas', 'barang_diambil', 'barang_kembali', 'selesai']);
                    @endphp
                    <p class="text-xs text-amber-400/80 italic mt-1">Admin wajib mencocokkan identitas fisik penyewa, nomor telepon, dan data profil saat pengambilan alat.</p>
                    <ul class="text-xs space-y-1.5 mt-2">
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isEmailVerified ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isEmailVerified ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isEmailVerified ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Email Terverifikasi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isConsentAccepted ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isConsentAccepted ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isConsentAccepted ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Persetujuan Tanggung Jawab</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isProfileComplete ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isProfileComplete ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isProfileComplete ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Profil Lengkap</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isPhoneAvailable ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isPhoneAvailable ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isPhoneAvailable ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Nomor Telepon Tersedia</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isEmergencyAvailable ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isEmergencyAvailable ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isEmergencyAvailable ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Kontak Darurat Tersedia</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isAddressAvailable ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isAddressAvailable ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isAddressAvailable ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Alamat / Google Maps Tersedia</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isPaymentPaid ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isPaymentPaid ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isPaymentPaid ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Pembayaran Lunas (Paid)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] {{ $isReadyForPickup ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                {!! $isReadyForPickup ? '✓' : '✗' !!}
                            </span>
                            <span class="{{ $isReadyForPickup ? 'text-[#E8E8EC]' : 'text-rose-300/80' }}">Status Siap Diambil / Seterusnya</span>
                        </li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Status Pembayaran') }}</label>
                        @if (auth('admin')->user()->role === 'super_admin')
                            <select name="status_pembayaran" class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                @foreach (['pending', 'paid', 'failed', 'expired', 'refunded'] as $paymentStatus)
                                    @php
                                        $paymentStatusText = match ($paymentStatus) {
                                            'paid' => __('Lunas'),
                                            'failed' => __('Gagal'),
                                            'expired' => __('Kedaluwarsa'),
                                            'refunded' => __('Refund'),
                                            default => __('Menunggu'),
                                        };
                                    @endphp
                                    <option value="{{ $paymentStatus }}" {{ $order->status_pembayaran === $paymentStatus ? 'selected' : '' }}>
                                        {{ $paymentStatusText }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <select name="status_pembayaran_disabled" class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed" disabled>
                                <option value="{{ $order->status_pembayaran }}" selected>
                                    {{ $paymentLabel($order->status_pembayaran) }}
                                </option>
                            </select>
                            <input type="hidden" name="status_pembayaran" value="{{ $order->status_pembayaran }}">
                        @endif
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Status Pesanan') }}</label>
                        <select name="status_pesanan" class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                            @foreach ($statusPesananOptions as $orderStatus)
                                <option value="{{ $orderStatus }}" {{ $order->status_pesanan === $orderStatus ? 'selected' : '' }}>
                                    {{ $statusLabel($orderStatus) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Biaya Tambahan') }}</label>
                        <input
                            type="number"
                            min="0"
                            step="1000"
                            name="additional_fee"
                            value="{{ old('additional_fee', (int) ($order->additional_fee ?? 0)) }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Keterangan Biaya Tambahan') }}</label>
                        <input
                            type="text"
                            name="additional_fee_note"
                            value="{{ old('additional_fee_note', $order->additional_fee_note) }}"
                            placeholder="{{ __('Contoh: Denda keterlambatan 1 hari') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">{{ __('Catatan Admin ke Pengguna') }}</label>
                        <textarea
                            name="admin_note"
                            rows="3"
                            placeholder="{{ __('Contoh: Barang ditemukan rusak pada bagian tombol record') }}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                        >{{ old('admin_note', $order->admin_note) }}</textarea>
                    </div>

                    <p class="text-xs text-slate-500">{{ __('Setiap perubahan status/biaya/catatan otomatis dikirim ke notifikasi pengguna.') }}</p>

                    <button class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        {{ __('Simpan & Kirim Notifikasi') }}
                    </button>
                </form>

                <div class="border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-semibold text-slate-900">{{ __('Log Pesanan') }}</h4>
                        <span class="text-[11px] text-slate-400">{{ __('Terbaru') }}</span>
                    </div>

                    <div class="mt-3 space-y-2.5">
                        @forelse (($auditLogs ?? collect()) as $log)
                            <article class="rounded-2xl border border-slate-200 px-3 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-xs font-semibold text-slate-800">{{ $log['summary'] }}</p>
                                    <span class="shrink-0 text-[11px] text-slate-400">{{ optional($log['created_at'])->format('d M H:i') }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $log['admin_name'] ?: __('Sistem') }}</p>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 px-3 py-4 text-sm text-slate-500">
                                {{ __('Belum ada log untuk pesanan ini.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </section>
    </div>
@endsection

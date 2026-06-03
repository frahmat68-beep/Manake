@extends('layouts.admin', ['activePage' => 'users'])

@section('title', __('ui.admin_users_detail.title'))
@section('page_title', __('ui.admin_users_detail.page_title'))

@push('head')
<style>
    .admin-user-detail-page {
        color: var(--admin-text);
    }

    .admin-user-detail-card {
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        color: var(--admin-text);
        border-radius: 1.35rem;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.45);
    }

    html[data-theme-resolved="light"] .admin-user-detail-card {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        box-shadow: 0 22px 55px -38px rgba(15,23,42,0.22);
    }

    html[data-theme-resolved="dark"] .admin-user-detail-card {
        background: #111113 !important;
        border-color: #1A1A1E !important;
        box-shadow: 0 18px 50px -36px rgba(0,0,0,0.65);
    }

    .admin-user-detail-title {
        color: var(--admin-text);
    }

    .admin-user-detail-muted {
        color: var(--admin-muted);
    }

    .admin-user-detail-subtle {
        color: var(--admin-subtle);
    }

    .admin-user-detail-kicker {
        color: var(--admin-accent);
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }

    .admin-user-detail-field {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface-raised);
        border-radius: 1rem;
        padding: 0.85rem 1rem;
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-user-detail-field {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-user-detail-field {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
    }

    .admin-user-detail-label {
        display: block;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--admin-subtle);
    }

    .admin-user-detail-value {
        margin-top: 0.3rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--admin-text);
        overflow-wrap: anywhere;
    }

    .admin-user-detail-input {
        width: 100%;
        min-height: 2.9rem;
        border: 1px solid var(--admin-border);
        background: var(--admin-surface);
        color: var(--admin-text);
        border-radius: 0.9rem;
        padding: 0 0.9rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
    }

    .admin-user-detail-input:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 3px var(--admin-accent-soft);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-user-detail-input {
        background: #FFFFFF !important;
        border-color: #E5E7EB !important;
        color: #111827 !important;
        color-scheme: light;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-user-detail-input {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
        color: #E8E8EC !important;
        color-scheme: dark;
    }

    .admin-user-detail-order {
        border: 1px solid var(--admin-border);
        background: var(--admin-surface-raised);
        color: var(--admin-text);
    }

    html[data-theme-resolved="light"] body[data-manake-shell="admin"] .admin-user-detail-order {
        background: #F8FAFC !important;
        border-color: #E5E7EB !important;
    }

    html[data-theme-resolved="dark"] body[data-manake-shell="admin"] .admin-user-detail-order {
        background: #0A0A0B !important;
        border-color: #1A1A1E !important;
    }
</style>
@endpush

@section('content')
    @php
        $profile = $user->profile;
        $addressText = $profile?->address_text ?? '-';
        $safeMapsUrl = trusted_map_embed_url((string) ($profile?->maps_url ?? ''), $addressText !== '-' ? $addressText : null);

        $userDetailCopy = __('ui.admin_users_detail');

        if (! is_array($userDetailCopy)) {
            $userDetailCopy = [
                'title' => 'User Details',
                'page_title' => 'User Details',
                'kicker' => 'Users',
                'back_to_users' => '← Back to Users',
                'profile' => [
                    'title' => 'User Profile',
                    'full_name' => 'Full Name',
                    'national_id' => 'National ID',
                    'date_of_birth' => 'Date of Birth',
                    'gender' => 'Gender',
                    'phone' => 'Phone Number',
                    'role' => 'Role',
                    'address' => 'Address',
                    'google_maps' => 'Google Maps',
                    'open_map' => 'Open Link',
                    'postal_code' => 'Postal Code',
                    'emergency_contact' => 'Emergency Contact',
                    'emergency_relation' => 'Emergency Relation',
                    'emergency_phone' => 'Emergency Phone',
                    'email_status' => 'Email Status',
                    'phone_status' => 'Phone Status',
                    'profile_status' => 'Profile Status',
                    'completed_at' => 'Completed At',
                ],
                'status' => [
                    'verified' => 'Verified',
                    'unverified' => 'Unverified',
                    'complete' => 'Complete',
                    'incomplete' => 'Incomplete',
                ],
                'orders' => [
                    'title' => 'Latest Orders',
                    'empty' => 'No orders yet.',
                    'view' => 'View',
                    'payment_paid' => 'PAID',
                    'payment_failed' => 'FAILED',
                    'payment_pending' => 'PENDING',
                    'waiting_payment' => 'Waiting for Payment',
                    'processed' => 'Processed',
                    'ready_for_pickup' => 'Ready for Pickup',
                    'picked_up' => 'Picked Up',
                    'returned' => 'Returned',
                    'damaged' => 'Damaged',
                    'lost' => 'Lost',
                    'overdue_fee' => 'Overdue Fee',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'refund' => 'Refund',
                ],
                'security' => [
                    'title' => 'Security Actions',
                    'description' => 'User passwords are not displayed to admin. The system only stores the password hash.',
                    'set_password_title' => 'Set New Password',
                    'new_password' => 'New Password',
                    'confirm_new_password' => 'Confirm New Password',
                    'save_new_password' => 'Save New Password',
                    'send_reset_link' => 'Send Password Reset Link by Email',
                ],
            ];
        }

        $formatStatus = function (bool $ok, ?string $okText = null, ?string $noText = null) use ($userDetailCopy): string {
            $okLabel = $okText ?? $userDetailCopy['status']['verified'];
            $noLabel = $noText ?? $userDetailCopy['status']['unverified'];

            return $ok
                ? '<span class="status-chip status-chip-success">' . $okLabel . '</span>'
                : '<span class="status-chip status-chip-warning">' . $noLabel . '</span>';
        };

        $formatProfileStatus = fn (bool $ok) => $ok
            ? '<span class="status-chip status-chip-info">' . $userDetailCopy['status']['complete'] . '</span>'
            : '<span class="status-chip status-chip-muted">' . $userDetailCopy['status']['incomplete'] . '</span>';

        $formatOrderStatus = fn (?string $status) => match ((string) $status) {
            'menunggu_pembayaran' => $userDetailCopy['orders']['waiting_payment'],
            'diproses' => $userDetailCopy['orders']['processed'],
            'lunas' => $userDetailCopy['orders']['ready_for_pickup'],
            'barang_diambil' => $userDetailCopy['orders']['picked_up'],
            'barang_kembali' => $userDetailCopy['orders']['returned'],
            'barang_rusak' => $userDetailCopy['orders']['damaged'],
            'barang_hilang' => $userDetailCopy['orders']['lost'],
            'overdue_denda' => $userDetailCopy['orders']['overdue_fee'],
            'selesai' => $userDetailCopy['orders']['completed'],
            'dibatalkan' => $userDetailCopy['orders']['cancelled'],
            'refund' => $userDetailCopy['orders']['refund'],
            default => strtoupper((string) $status),
        };
    @endphp

    <div class="admin-user-detail-page mx-auto max-w-6xl space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="admin-user-detail-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="admin-user-detail-kicker">{{ $userDetailCopy['kicker'] }}</p>
                    <h2 class="admin-user-detail-title mt-2 text-3xl font-black">
                        {{ $user->name }}
                    </h2>
                    <p class="admin-user-detail-muted mt-1 text-sm">
                        {{ $user->email }}
                    </p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="admin-secondary-button inline-flex min-h-10 items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                    {{ $userDetailCopy['back_to_users'] }}
                </a>
            </div>
        </div>

        <section class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1.35fr),minmax(340px,0.65fr)]">
            <article class="admin-user-detail-card p-5 sm:p-6">
                <h3 class="admin-user-detail-title text-xl font-black">
                    {{ $userDetailCopy['profile']['title'] }}
                </h3>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['full_name'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->full_name ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['national_id'] }}</span>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <p class="admin-user-detail-value m-0">
                                @if (isset($revealNik) && $revealNik)
                                    {{ $profile?->nik ?? $profile?->identity_number ?? '-' }}
                                @else
                                    {{ $profile?->masked_nik ?? '-' }}
                                @endif
                            </p>
                            @if (auth('admin')->user()->role === 'super_admin' && (!isset($revealNik) || !$revealNik))
                                <a href="?reveal_nik=1" onclick="return confirm('Data NIK bersifat sensitif. Gunakan hanya untuk verifikasi penyewaan.')" class="text-xs font-bold text-[var(--admin-accent)] hover:underline border border-[var(--admin-accent)]/20 rounded px-2 py-1">
                                    Tampilkan NIK Lengkap
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['date_of_birth'] }}</span>
                        <p class="admin-user-detail-value">{{ optional($profile?->date_of_birth)->format('d M Y') ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['gender'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->gender ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['phone'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->phone ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">No. Telepon Alternatif</span>
                        <p class="admin-user-detail-value">{{ $profile?->alternative_phone ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">Instagram Username</span>
                        <p class="admin-user-detail-value">{{ $profile?->instagram_handle ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">Nama Instansi/Organisasi</span>
                        <p class="admin-user-detail-value">{{ $profile?->organization_name ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">Jenis Instansi</span>
                        <p class="admin-user-detail-value">
                            @if (($profile?->organization_type ?? '') === 'student')
                                Kemahasiswaan / Sekolah
                            @elseif (($profile?->organization_type ?? '') === 'production_house')
                                Production House (PH)
                            @elseif (($profile?->organization_type ?? '') === 'freelance')
                                Pekerja Lepas / Freelance
                            @elseif (($profile?->organization_type ?? '') === 'event_organizer')
                                Event Organizer (EO)
                            @elseif (($profile?->organization_type ?? '') === 'general')
                                Umum / Lainnya
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['role'] }}</span>
                        <p class="admin-user-detail-value">{{ strtoupper($user->role ?? 'USER') }}</p>
                    </div>

                    <div class="admin-user-detail-field sm:col-span-2">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['address'] }}</span>
                        <p class="admin-user-detail-value">{{ $addressText }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['google_maps'] }}</span>
                        <p class="admin-user-detail-value">
                            @if ($safeMapsUrl)
                                <a href="{{ $safeMapsUrl }}" target="_blank" rel="noopener noreferrer" class="text-[var(--admin-accent)] hover:underline">
                                    {{ $userDetailCopy['profile']['open_map'] }}
                                </a>
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['postal_code'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->postal_code ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['emergency_contact'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->emergency_name ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['emergency_relation'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->emergency_relation ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['emergency_phone'] }}</span>
                        <p class="admin-user-detail-value">{{ $profile?->emergency_phone ?? '-' }}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['email_status'] }}</span>
                        <p class="admin-user-detail-value">{!! $formatStatus((bool) $user->email_verified_at) !!}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['phone_status'] }}</span>
                        <p class="admin-user-detail-value">{!! $formatStatus((bool) ($profile?->phone_verified_at)) !!}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['profile_status'] }}</span>
                        <p class="admin-user-detail-value">{!! $formatProfileStatus($user->profileIsComplete()) !!}</p>
                    </div>

                    <div class="admin-user-detail-field">
                        <span class="admin-user-detail-label">{{ $userDetailCopy['profile']['completed_at'] }}</span>
                        <p class="admin-user-detail-value">{{ optional($profile?->completed_at)->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="admin-user-detail-title text-base font-black">
                        {{ $userDetailCopy['orders']['title'] }}
                    </h4>
                    <div class="mt-3 space-y-2">
                        @forelse ($user->orders as $order)
                            @php
                                $paymentLabel = match ((string) ($order->status_pembayaran ?? 'pending')) {
                                    'paid' => $userDetailCopy['orders']['payment_paid'],
                                    'failed' => $userDetailCopy['orders']['payment_failed'],
                                    default => $userDetailCopy['orders']['payment_pending'],
                                };
                            @endphp
                            <div class="admin-user-detail-order flex items-center justify-between gap-3 rounded-xl p-3 text-sm">
                                <div>
                                    <p class="font-bold admin-user-detail-title">{{ $order->order_number ?? ('ORD-' . $order->id) }}</p>
                                    <p class="text-xs admin-user-detail-muted">{{ $paymentLabel }} • {{ $formatOrderStatus($order->status_pesanan) }}</p>
                                </div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-[var(--admin-accent)] hover:underline">
                                    {{ $userDetailCopy['orders']['view'] }}
                                </a>
                            </div>
                        @empty
                            <p class="text-sm admin-user-detail-muted">{{ $userDetailCopy['orders']['empty'] }}</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <aside class="admin-user-detail-card space-y-4 p-5 sm:p-6">
                <h3 class="admin-user-detail-title text-xl font-black">
                    {{ $userDetailCopy['security']['title'] }}
                </h3>
                <p class="admin-user-detail-muted text-sm">
                    {{ $userDetailCopy['security']['description'] }}
                </p>

                @if (auth('admin')->user()->role === 'super_admin')
                <form method="POST" action="{{ route('admin.users.set-password', $user) }}" class="space-y-4 rounded-2xl border border-[var(--admin-border)] bg-transparent p-4">
                    @csrf
                    <p class="admin-user-detail-title text-sm font-black">
                        {{ $userDetailCopy['security']['set_password_title'] }}
                    </p>
                    <div>
                        <label class="admin-user-detail-label">{{ $userDetailCopy['security']['new_password'] }}</label>
                        <input
                            type="password"
                            name="new_password"
                            class="admin-user-detail-input mt-2"
                            required
                        >
                    </div>
                    <div>
                        <label class="admin-user-detail-label">{{ $userDetailCopy['security']['confirm_new_password'] }}</label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            class="admin-user-detail-input mt-2"
                            required
                        >
                    </div>
                    <button class="admin-accent-bg inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                        {{ $userDetailCopy['security']['save_new_password'] }}
                    </button>
                </form>
                @endif

                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                    @csrf
                    <button class="admin-secondary-button inline-flex min-h-11 w-full items-center justify-center rounded-xl px-4 text-sm font-bold transition">
                        {{ $userDetailCopy['security']['send_reset_link'] }}
                    </button>
                </form>
            </aside>
        </section>
    </div>
@endsection

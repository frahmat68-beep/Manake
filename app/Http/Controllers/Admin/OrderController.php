<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PAYMENT_STATUS_OPTIONS = ['pending', 'paid', 'failed', 'expired', 'refunded'];

    private const ORDER_STATUS_OPTIONS = [
        'menunggu_pembayaran',
        'diproses',
        'lunas',
        'barang_diambil',
        'barang_kembali',
        'barang_rusak',
        'barang_hilang',
        'overdue_denda',
        'selesai',
        'expired',
        'dibatalkan',
        'refund',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $status = $request->string('status')->trim()->value();

        $orders = Order::query()
            ->with(['user', 'items'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', '%' . $search . '%')
                        ->orWhere('midtrans_order_id', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when(in_array($status, self::PAYMENT_STATUS_OPTIONS, true), fn ($query) => $query->where('status_pembayaran', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'activePage' => 'orders',
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user.profile', 'items.equipment', 'payment']);

        return view('admin.orders.show', [
            'order' => $order,
            'statusPesananOptions' => self::ORDER_STATUS_OPTIONS,
            'activePage' => 'orders',
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status_pembayaran' => ['required', 'in:' . implode(',', self::PAYMENT_STATUS_OPTIONS)],
            'status_pesanan' => ['required', 'in:' . implode(',', self::ORDER_STATUS_OPTIONS)],
            'additional_fee' => ['nullable', 'integer', 'min:0'],
            'additional_fee_note' => ['nullable', 'string', 'max:500'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $before = [
            'status_pembayaran' => $order->status_pembayaran,
            'status_pesanan' => $order->status_pesanan,
            'additional_fee' => (int) ($order->additional_fee ?? 0),
            'additional_fee_note' => (string) ($order->additional_fee_note ?? ''),
            'admin_note' => (string) ($order->admin_note ?? ''),
        ];

        if ($order->status_pesanan !== $data['status_pesanan'] && ! $order->canTransitionToOrderStatus($data['status_pesanan'])) {
            return back()->with('error', __('Transisi status pesanan tidak valid dari status saat ini.'));
        }

        $order->status_pembayaran = $data['status_pembayaran'];
        $order->status_pesanan = $data['status_pesanan'];
        $order->status = match ($data['status_pembayaran']) {
            'paid' => 'paid',
            'failed' => 'failed',
            'expired' => 'expired',
            'refunded' => 'refunded',
            default => 'pending',
        };
        $order->additional_fee = (int) ($data['additional_fee'] ?? 0);
        $order->additional_fee_note = trim((string) ($data['additional_fee_note'] ?? '')) ?: null;
        $order->admin_note = trim((string) ($data['admin_note'] ?? '')) ?: null;

        if ($order->status_pembayaran === 'paid' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        if ($order->status_pembayaran !== 'paid') {
            $order->paid_at = null;
        }

        if ($order->status_pesanan === 'barang_diambil' && ! $order->picked_up_at) {
            $order->picked_up_at = now();
        }

        if (in_array($order->status_pesanan, ['barang_kembali', 'barang_rusak', 'barang_hilang', 'selesai'], true) && ! $order->returned_at) {
            $order->returned_at = now();
        }

        if (in_array($order->status_pesanan, ['barang_rusak', 'barang_hilang'], true) && ! $order->damaged_at) {
            $order->damaged_at = now();
        }

        $order->save();

        $changes = $this->buildUserNotificationChanges($before, $order);
        if ($changes !== [] && Schema::hasTable('order_notifications')) {
            OrderNotification::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'type' => 'order_update',
                'title' => __('Update pesanan') . ' ' . ($order->order_number ?: ('ORD-' . $order->id)),
                'message' => implode(' ', $changes),
            ]);
        }

        admin_audit('order.update_status', 'orders', $order->id, [
            'status_pembayaran' => $order->status_pembayaran,
            'status_pesanan' => $order->status_pesanan,
            'status' => $order->status,
            'additional_fee' => $order->additional_fee,
            'additional_fee_note' => $order->additional_fee_note,
            'admin_note' => $order->admin_note,
        ], auth('admin')->id());

        return back()->with('success', __('Status order, biaya tambahan, dan notifikasi user berhasil diperbarui.'));
    }

    private function buildUserNotificationChanges(array $before, Order $order): array
    {
        $messages = [];

        if ($before['status_pembayaran'] !== $order->status_pembayaran) {
            $messages[] = __('Status pembayaran: :before → :after.', [
                'before' => $this->paymentStatusLabel($before['status_pembayaran']),
                'after' => $this->paymentStatusLabel($order->status_pembayaran),
            ]);
        }

        if ($before['status_pesanan'] !== $order->status_pesanan) {
            $messages[] = __('Status rental: :before → :after.', [
                'before' => $this->orderStatusLabel($before['status_pesanan']),
                'after' => $this->orderStatusLabel($order->status_pesanan),
            ]);
        }

        if ((int) $before['additional_fee'] !== (int) ($order->additional_fee ?? 0)) {
            $messages[] = __('Biaya tambahan diperbarui menjadi Rp :amount.', [
                'amount' => number_format((int) $order->additional_fee, 0, ',', '.'),
            ]);
        }

        if ($before['additional_fee_note'] !== (string) ($order->additional_fee_note ?? '')) {
            if (! empty($order->additional_fee_note)) {
                $messages[] = __('Catatan biaya: :note.', [
                    'note' => $order->additional_fee_note,
                ]);
            }
        }

        if ($before['admin_note'] !== (string) ($order->admin_note ?? '')) {
            if (! empty($order->admin_note)) {
                $messages[] = __('Catatan admin: :note.', [
                    'note' => $order->admin_note,
                ]);
            }
        }

        return $messages;
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => __('Lunas'),
            'failed' => __('Gagal'),
            'expired' => __('Kedaluwarsa'),
            'refunded' => __('Refund'),
            default => __('Menunggu'),
        };
    }

    private function orderStatusLabel(?string $status): string
    {
        return match ($status) {
            'menunggu_pembayaran' => __('Menunggu Pembayaran'),
            'diproses' => __('Diproses Admin'),
            'lunas' => __('Siap Diambil'),
            'barang_diambil' => __('Barang Diambil'),
            'barang_kembali' => __('Barang Dikembalikan'),
            'barang_rusak' => __('Barang Rusak'),
            'barang_hilang' => __('Barang Hilang'),
            'overdue_denda' => __('Denda Overdue'),
            'expired' => __('Kedaluwarsa'),
            'selesai' => __('Selesai'),
            'dibatalkan' => __('Dibatalkan'),
            'refund' => __('Refund'),
            default => strtoupper((string) $status),
        };
    }
}

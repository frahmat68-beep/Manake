<?php

namespace App\Http\Controllers;

use App\Models\OrderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = OrderNotification::query()
            ->with('order')
            ->where('user_id', (int) $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, OrderNotification $notification): JsonResponse|RedirectResponse
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->forceFill([
                'read_at' => now(),
            ])->save();
        }

        $unreadCount = (int) OrderNotification::query()
            ->where('user_id', (int) $request->user()->id)
            ->whereNull('read_at')
            ->count();

        if (! $request->expectsJson()) {
            $target = (string) $request->input('redirect', route('notifications'));

            return redirect($target)->with('status', __('Notifikasi diperbarui.'));
        }

        return response()->json([
            'ok' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}

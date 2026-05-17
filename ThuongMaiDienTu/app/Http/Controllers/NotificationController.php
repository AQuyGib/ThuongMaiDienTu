<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'notifications' => $this->notificationService->listForUser($user, 12),
            'unreadCount' => $this->notificationService->unreadCountForUser($user),
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->notificationService->unreadCountForUser($request->user()),
        ]);
    }

    public function markAsRead(Notification $notification, Request $request): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->user_id, 403);

        $this->notificationService->markAsRead($notification);

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}

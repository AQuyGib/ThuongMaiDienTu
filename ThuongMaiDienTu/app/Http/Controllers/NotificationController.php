<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý các hoạt động tương tác với thông báo của phía người dùng (Client-side / Customer portal).
 * Cung cấp danh sách thông báo, số lượng chưa đọc, và xử lý đánh dấu đã đọc thông báo qua AJAX hoặc chuyển hướng truyền thống.
 */
class NotificationController extends Controller
{
    /**
     * Khởi tạo Controller với NotificationService.
     */
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Hiển thị trang trung tâm thông báo của người dùng hiện tại.
     * Hỗ trợ phân trang và đếm tổng số thông báo chưa đọc để hiển thị badge.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            // Phân trang 12 thông báo gần nhất của người dùng
            'notifications' => $this->notificationService->listForUser($user, 12),
            // Đếm số lượng thông báo chưa đọc
            'unreadCount' => $this->notificationService->unreadCountForUser($user),
        ]);
    }

    /**
     * API JSON trả về số lượng thông báo chưa đọc của người dùng.
     * Thường dùng để cập nhật realtime số lượng thông báo trên thanh topbar / quả chuông.
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->notificationService->unreadCountForUser($request->user()),
        ]);
    }

    /**
     * Đánh dấu một thông báo cụ thể là đã đọc (Ghi nhận thời gian read_at).
     * Yêu cầu xác thực người dùng hiện tại chính là chủ sở hữu của thông báo đó (tránh 403).
     */
    public function markAsRead(Notification $notification, Request $request)
    {
        // Kiểm tra quyền sở hữu thông báo
        abort_unless((int)$notification->user_id === (int)$request->user()->user_id, 403);

        $this->notificationService->markAsRead($notification);

        // Hỗ trợ trả về JSON cho AJAX hoặc redirect back cho request HTTP bình thường
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã đánh dấu thông báo là đã đọc.']);
        }

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    /**
     * Đánh dấu tất cả các thông báo chưa đọc của người dùng hiện tại là đã đọc.
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        // Phản hồi JSON nếu gọi qua AJAX từ trang thông báo
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.']);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}


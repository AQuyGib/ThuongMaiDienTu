<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

/**
 * =================================================================================
 * BỘ ĐIỀU KHIỂN: XEM VIDEO SẢN PHẨM & TƯƠNG TÁC (VIDEO CONTROLLER)
 * ---------------------------------------------------------------------------------
 * Đây là nơi quản lý giao diện xem video giới thiệu/đánh giá sản phẩm. Khách hàng có thể 
 * xem video, bấm Thích (Like), để lại bình luận dưới video, hoặc báo cáo các bình luận 
 * vi phạm thuần phong mỹ tục. Ngoài ra bộ điều khiển này cũng tự động đếm lượt xem video.
 * =================================================================================
 */
class VideoController extends Controller
{
    /**
     * HÀM: index
     * Ý NGHĨA: Hiển thị trang danh sách video và các danh mục sản phẩm tương ứng.
     */
    public function index(Request $request)
    {
        // 1. Lấy toàn bộ các video có trạng thái "đã xuất bản" (published) sắp xếp theo thứ tự mới nhất
        $videos = Video::query()
            ->where('status', 'published')
            ->latest()
            ->get();

        // 2. Lấy danh sách các danh mục có sản phẩm để hiển thị làm bộ lọc tìm kiếm nhanh
        $categories = \App\Models\Category::whereHas('products')->orderBy('name')->get();

        // 3. Trả về view giao diện kèm dữ liệu video và danh mục
        return view('videos.index', compact('videos', 'categories'));
    }

    /**
     * HÀM: like
     * Ý NGHĨA: Thực hiện thích hoặc hủy thích một video.
     */
    public function like(Request $request, Video $video)
    {
        // 1. Lấy hành động được gửi lên (mặc định là 'like', hoặc có thể là 'unlike')
        $action = $request->input('action', 'like');

        // 2. Nếu là thích -> Tăng lượt thích lên 1
        if ($action === 'like') {
            $video->increment('likes');
        } else {
            // 3. Nếu là hủy thích và lượt thích hiện tại lớn hơn 0 -> Giảm lượt thích đi 1
            if ($video->likes > 0) {
                $video->decrement('likes');
            }
        }

        // 4. Trả về kết quả lượt thích mới nhất dưới dạng JSON
        return response()->json([
            'success' => true,
            'likes' => $video->fresh()->likes,
        ]);
    }

    /**
     * HÀM: view
     * Ý NGHĨA: Tăng lượt xem (views) khi người dùng mở xem video.
     */
    public function view(Video $video)
    {
        // 1. Tăng số lượt xem của video tương ứng lên 1 đơn vị
        $video->increment('views');

        // 2. Trả về số lượt xem mới của video
        return response()->json([
            'success' => true,
            'views' => $video->fresh()->views,
        ]);
    }

    /**
     * HÀM: getComments
     * Ý NGHĨA: Lấy danh sách các bình luận hợp lệ (đã duyệt) của video để hiển thị.
     */
    public function getComments(Video $video)
    {
        // 1. Tìm các bình luận gốc (không có parent_id) và đã được duyệt (is_approved = 1)
        // Kèm theo thông tin người viết bình luận và các phản hồi cấp 2 đã được duyệt
        $commentsQuery = $video->comments()
            ->whereNull('parent_id')
            ->where('is_approved', 1)
            ->with(['user', 'replies' => function ($query) {
                $query->where('is_approved', 1)->with('user');
            }])
            ->get();
            
        // 2. Đếm tổng số lượng bình luận đã được duyệt của video này
        $totalCount = $video->comments()->where('is_approved', 1)->count();
        
        // 3. Chuẩn hóa lại cấu trúc dữ liệu bình luận để trả về phía frontend
        $comments = $commentsQuery->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('d/m/Y H:i'),
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user->full_name ?? $comment->user->name ?? 'Người dùng',
                    'role_id' => $comment->user->role_id ?? 3,
                ],
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'created_at' => $reply->created_at->format('d/m/Y H:i'),
                        'user' => [
                            'id' => $reply->user_id,
                            'name' => $reply->user->full_name ?? $reply->user->name ?? 'Người dùng',
                            'role_id' => $reply->user->role_id ?? 3,
                        ]
                    ];
                })
            ];
        });

        // 4. Trả kết quả JSON về cho ứng dụng
        return response()->json([
            'success' => true,
            'comments' => $comments,
            'total_count' => $totalCount,
        ]);
    }

    /**
     * HÀM: storeComment
     * Ý NGHĨA: Lưu bình luận mới của người dùng (tự động kiểm duyệt ngôn từ nhạy cảm).
     */
    public function storeComment(Request $request, Video $video)
    {
        // 1. Kiểm tra đăng nhập
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi bình luận!'
            ], 401);
        }

        // 2. KIỂM TRA HÌNH PHẠT CẤM HOẠT ĐỘNG:
        // - Xem người dùng hiện tại có bị khóa bình luận không (comment_banned_until).
        // - Nếu còn thời hạn khóa, chặn hành động và trả về thông báo kèm thời hạn mở khóa cụ thể.
        $user = auth()->user();
        if ($user->comment_banned_until && $user->comment_banned_until > now()) {
            $isPermanent = \Carbon\Carbon::parse($user->comment_banned_until)->year > now()->year + 10;
            $msg = $isPermanent 
                ? "Tài khoản của bạn đã bị cấm bình luận/đánh giá vĩnh viễn do vi phạm chính sách cộng đồng."
                : "Tài khoản của bạn đã bị khóa tính năng bình luận/đánh giá đến " . \Carbon\Carbon::parse($user->comment_banned_until)->format('d/m/Y H:i') . " do vi phạm chính sách cộng đồng.";
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 403);
        }

        // 3. Kiểm tra tính hợp lệ của nội dung bình luận
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:video_comments,id',
        ]);

        // 4. Tự động kiểm duyệt: Quản trị viên/Quản lý thì bỏ qua, người dùng thường thì quét từ khóa nhạy cảm
        $isAdmin = (auth()->check() && in_array(auth()->user()->role_id, [1, 2]));
        if ($isAdmin) {
            $isApproved = 1;
        } else {
            $moderator = app(\App\Services\CommentModerationService::class);
            $isApproved = $moderator->isSafe($request->content) ? 1 : 0;
        }

        // 5. Lưu thông tin bình luận vào cơ sở dữ liệu
        $comment = \App\Models\VideoComment::create([
            'video_id' => $video->id,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_approved' => $isApproved,
        ]);

        // 6. Phản hồi trạng thái đăng thành công hoặc chờ duyệt
        return response()->json([
            'success' => true,
            'message' => $isApproved ? 'Bình luận thành công!' : 'Bình luận của bạn chứa từ khóa nhạy cảm và đang chờ kiểm duyệt!',
            'comment' => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'content' => $comment->content,
                'is_approved' => $comment->is_approved,
                'created_at' => $comment->created_at->format('d/m/Y H:i'),
                'user' => [
                    'name' => auth()->user()->full_name ?? auth()->user()->name ?? 'Người dùng',
                    'role_id' => auth()->user()->role_id ?? 3,
                ]
            ],
        ], 201);
    }

    /**
     * HÀM: destroyComment
     * Ý NGHĨA: Xóa một bình luận video.
     */
    public function destroyComment(\App\Models\VideoComment $comment)
    {
        // 1. Chỉ cho phép quản trị viên, quản lý hoặc chính người viết bình luận đó thực hiện xóa
        if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->id() == $comment->user_id) {
            $comment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa bình luận thành công!'
            ]);
        }

        // 2. Báo lỗi nếu cố tình xóa bình luận của người khác
        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ], 403);
    }

    /**
     * HÀM: reportComment
     * Ý NGHĨA: Báo cáo bình luận video vi phạm.
     * - Khi nhận được báo cáo từ người dùng, tăng số lượt báo cáo `report_count`.
     * - Nếu tổng số báo cáo của bình luận đạt ngưỡng >= 3, bình luận sẽ tự động bị ẩn (is_approved = 0) chờ kiểm duyệt của Admin.
     */
    public function reportComment($id)
    {
        $comment = \App\Models\VideoComment::findOrFail($id);
        
        // Tăng bộ đếm lượt báo cáo vi phạm
        $comment->increment('report_count');

        // Ngưỡng ẩn tự động khi đạt từ 3 báo cáo trở lên
        if ($comment->report_count >= 3) {
            $comment->update(['is_approved' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã báo cáo vi phạm. Ban quản trị sẽ sớm xem xét bình luận này!'
        ]);
    }

    /**
     * HÀM: stream
     * Ý NGHĨA: Hỗ trợ phát video dạng phân đoạn dữ liệu (Streaming Byte-Range).
     * Giúp tải video mượt mà, tua nhanh/tua ngược nhanh chóng trên trình phát trình duyệt.
     */
    public function stream(Video $video)
    {
        // 1. Kiểm tra tính tồn tại của đường dẫn video
        if (empty($video->video_path)) {
            abort(404, 'Video path not found.');
        }

        $path = public_path($video->video_path);
        
        // 2. Tìm kiếm file video trong thư mục public hoặc storage
        if (!file_exists($path)) {
            $path = storage_path('app/public/' . $video->video_path);
        }
        
        // 3. Nếu là đường dẫn URL bên ngoài thì chuyển hướng trình duyệt trực tiếp
        if (!file_exists($path)) {
            if (filter_var($video->video_path, FILTER_VALIDATE_URL)) {
                return redirect()->away($video->video_path);
            }
            abort(404, 'Video file does not exist.');
        }

        // 4. Mở file và lấy kích thước video
        $file = fopen($path, 'rb');
        $size = filesize($path);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        $headers = [
            'Content-Type' => $video->mime_type ?: 'video/mp4',
            'Accept-Ranges' => 'bytes',
        ];

        // 5. Xử lý yêu cầu phân đoạn Range Header từ phía trình duyệt (tua video)
        if (request()->header('Range')) {
            $range = request()->header('Range');
            if (preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
                $start = intval($matches[1]);
                if (isset($matches[2])) {
                    $end = intval($matches[2]);
                }
                $length = $end - $start + 1;
                
                fseek($file, $start);
                
                $headers['Content-Length'] = $length;
                $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
                
                // Trả về luồng dữ liệu 206 Partial Content
                return response()->stream(function() use ($file, $length) {
                    $buffer = 102400; // Đọc và gửi từng gói 100kb
                    $bytes_sent = 0;
                    while (!feof($file) && $bytes_sent < $length) {
                        if (connection_aborted()) break;
                        $to_send = min($buffer, $length - $bytes_sent);
                        echo fread($file, $to_send);
                        flush();
                        $bytes_sent += $to_send;
                    }
                    fclose($file);
                }, 206, $headers);
            }
        }

        // 6. Trường hợp phát toàn bộ video bình thường
        $headers['Content-Length'] = $length;
        return response()->stream(function() use ($file) {
            fpassthru($file);
            fclose($file);
        }, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $videos = Video::query()
            ->where('status', 'published')
            ->latest()
            ->get();

        $categories = \App\Models\Category::whereHas('products')->orderBy('name')->get();

        return view('videos.index', compact('videos', 'categories'));
    }


    public function like(Request $request, Video $video)
    {
        $action = $request->input('action', 'like');

        if ($action === 'like') {
            $video->increment('likes');
        } else {
            if ($video->likes > 0) {
                $video->decrement('likes');
            }
        }

        return response()->json([
            'success' => true,
            'likes' => $video->fresh()->likes,
        ]);
    }

    public function view(Video $video)
    {
        $video->increment('views');

        return response()->json([
            'success' => true,
            'views' => $video->fresh()->views,
        ]);
    }

    public function getComments(Video $video)
    {
        $commentsQuery = $video->comments()
            ->whereNull('parent_id')
            ->where('is_approved', 1)
            ->with(['user', 'replies' => function ($query) {
                $query->where('is_approved', 1)->with('user');
            }])
            ->get();
            
        $totalCount = $video->comments()->where('is_approved', 1)->count();
        
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

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'total_count' => $totalCount,
        ]);
    }

    /**
     * Tạo một bình luận mới cho Video.
     * 
     * @param Request $request Dữ liệu đầu vào của bình luận (content, parent_id).
     * @param Video $video Thực thể Video liên kết.
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeComment(Request $request, Video $video)
    {
        // Kiểm tra đăng nhập
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi bình luận!'
            ], 401);
        }

        // KIỂM TRA HÌNH PHẠT CẤM HOẠT ĐỘNG:
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

        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:video_comments,id',
        ]);

        $isAdmin = (auth()->check() && in_array(auth()->user()->role_id, [1, 2]));
        if ($isAdmin) {
            $isApproved = 1;
        } else {
            $moderator = app(\App\Services\CommentModerationService::class);
            $isApproved = $moderator->isSafe($request->content) ? 1 : 0;
        }

        $comment = \App\Models\VideoComment::create([
            'video_id' => $video->id,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_approved' => $isApproved,
        ]);

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

    public function destroyComment(\App\Models\VideoComment $comment)
    {
        if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->id() == $comment->user_id) {
            $comment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa bình luận thành công!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ], 403);
    }

    /**
     * Báo cáo bình luận video vi phạm.
     * - Khi nhận được báo cáo từ người dùng, tăng số lượt báo cáo `report_count`.
     * - Nếu tổng số báo cáo của bình luận đạt ngưỡng >= 3, bình luận sẽ tự động bị ẩn (is_approved = 0) chờ kiểm duyệt của Admin.
     * 
     * @param int $id ID của bình luận video cần báo cáo.
     * @return \Illuminate\Http\JsonResponse
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

    public function stream(Video $video)
    {
        if (empty($video->video_path)) {
            abort(404, 'Video path not found.');
        }

        $path = public_path($video->video_path);
        
        if (!file_exists($path)) {
            $path = storage_path('app/public/' . $video->video_path);
        }
        
        if (!file_exists($path)) {
            if (filter_var($video->video_path, FILTER_VALIDATE_URL)) {
                return redirect()->away($video->video_path);
            }
            abort(404, 'Video file does not exist.');
        }

        $file = fopen($path, 'rb');
        $size = filesize($path);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        $headers = [
            'Content-Type' => $video->mime_type ?: 'video/mp4',
            'Accept-Ranges' => 'bytes',
        ];

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
                
                return response()->stream(function() use ($file, $length) {
                    $buffer = 102400; // 100kb
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

        $headers['Content-Length'] = $length;
        return response()->stream(function() use ($file) {
            fpassthru($file);
            fclose($file);
        }, 200, $headers);
    }
}

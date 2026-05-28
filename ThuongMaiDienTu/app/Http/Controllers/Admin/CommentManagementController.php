<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\VideoComment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentManagementController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'reviews');

        $reviews = null;
        $videoComments = null;

        if ($tab === 'reviews') {
            $query = Review::with(['user', 'product', 'replies.user'])
                ->whereNull('parent_id')
                ->latest();

            if ($request->filled('keyword')) {
                $kw = '%' . $request->input('keyword') . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('content', 'like', $kw)
                      ->orWhere('author_name', 'like', $kw)
                      ->orWhereHas('user', function ($qu) use ($kw) {
                          $qu->where('name', 'like', $kw)
                            ->orWhere('full_name', 'like', $kw)
                            ->orWhere('email', 'like', $kw);
                      })
                      ->orWhereHas('product', function ($qp) use ($kw) {
                          $qp->where('name', 'like', $kw);
                      });
                });
            }

            if ($request->filled('rating')) {
                $query->where('rating', $request->integer('rating'));
            }

            $reviews = $query->paginate(15)->withQueryString();
        } else {
            $query = VideoComment::with(['user', 'video', 'replies.user'])
                ->whereNull('parent_id')
                ->latest();

            if ($request->filled('keyword')) {
                $kw = '%' . $request->input('keyword') . '%';
                $query->where(function ($q) use ($kw) {
                    $q->where('content', 'like', $kw)
                      ->orWhereHas('user', function ($qu) use ($kw) {
                          $qu->where('name', 'like', $kw)
                            ->orWhere('full_name', 'like', $kw)
                            ->orWhere('email', 'like', $kw);
                      })
                      ->orWhereHas('video', function ($qv) use ($kw) {
                          $qv->where('title', 'like', $kw);
                      });
                });
            }

            $videoComments = $query->paginate(15)->withQueryString();
        }

        // Đếm tổng số lượng để hiển thị badge
        $totalReviews = Review::whereNull('parent_id')->count();
        $totalVideoComments = VideoComment::whereNull('parent_id')->count();

        return view('admin.comments.index', compact(
            'tab',
            'reviews',
            'videoComments',
            'totalReviews',
            'totalVideoComments'
        ));
    }

    /**
     * Xóa đánh giá sản phẩm (Review) và áp dụng hình phạt cho người dùng (nếu có).
     * 
     * @param Request $request Chứa tham số 'penalty' thiết lập thời gian cấm hoạt động.
     * @param int $id ID của đánh giá sản phẩm cần xóa.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyReview(Request $request, $id)
    {
        // Tìm bản ghi đánh giá hoặc trả về 404
        $review = Review::findOrFail($id);
        
        $penalty = $request->input('penalty');
        $durationText = null;
        
        // Kiểm tra xem có yêu cầu cấm người dùng và người dùng có tồn tại không
        if ($penalty && $penalty !== 'none' && $review->user_id) {
            $user = User::find($review->user_id);
            if ($user) {
                $bannedUntil = null;
                // Áp dụng khoảng thời gian cấm dựa trên lựa chọn từ SweetAlert
                if ($penalty === '1') {
                    $bannedUntil = now()->addDay();
                    $durationText = '1 ngày';
                } elseif ($penalty === '3') {
                    $bannedUntil = now()->addDays(3);
                    $durationText = '3 ngày';
                } elseif ($penalty === 'permanent') {
                    $bannedUntil = now()->addYears(100);
                    $durationText = 'vĩnh viễn';
                }
                
                // Lưu thời hạn cấm vào cơ sở dữ liệu
                if ($bannedUntil) {
                    $user->update(['comment_banned_until' => $bannedUntil]);
                }
            }
        }
        
        // Gửi thông báo vi phạm đánh giá và thông báo cấm tài khoản (nếu có) đến người dùng
        if ($review->user) {
            // Thông báo nội dung bị xóa do vi phạm tiêu chuẩn cộng đồng
            app(NotificationService::class)->createForUser($review->user, [
                'type' => 'review.deleted',
                'title' => 'Đánh giá đã bị gỡ bỏ',
                'content' => 'Đánh giá của bạn cho sản phẩm #' . $review->product_id . ' đã bị xóa do vi phạm tiêu chuẩn cộng đồng.',
                'action_url' => '#',
                'data' => [
                    'product_id' => $review->product_id,
                ],
            ]);
            
            // Gửi thêm thông báo cấm nếu có áp dụng hình phạt
            if ($durationText && isset($bannedUntil)) {
                app(NotificationService::class)->createForUser($review->user, [
                    'type' => 'user.banned',
                    'title' => 'Tài khoản bị hạn chế',
                    'content' => 'Tài khoản của bạn đã bị cấm bình luận/đánh giá ' . $durationText . ' do vi phạm chính sách cộng đồng.',
                    'action_url' => '#',
                    'data' => [
                        'banned_until' => $bannedUntil->toIso8601String(),
                    ],
                ]);
            }
        }

        // Xóa các câu trả lời (reply) con liên kết với đánh giá cha này trước
        Review::where('parent_id', $review->id)->delete();
        
        // Xóa bản ghi đánh giá cha
        $review->delete();

        $msg = 'Đã xóa đánh giá thành công.';
        if ($durationText) {
            $msg .= ' Đồng thời đã cấm tài khoản viết bình luận này ' . $durationText . '.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa bình luận video (VideoComment) và áp dụng hình phạt cho người dùng (nếu có).
     * 
     * @param Request $request Chứa tham số 'penalty' thiết lập thời gian cấm hoạt động.
     * @param int $id ID của bình luận video cần xóa.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyVideoComment(Request $request, $id)
    {
        // Tìm bản ghi bình luận video hoặc trả về 404
        $comment = VideoComment::findOrFail($id);

        $penalty = $request->input('penalty');
        $durationText = null;
        
        // Kiểm tra xem có yêu cầu cấm người dùng và người dùng có tồn tại không
        if ($penalty && $penalty !== 'none' && $comment->user_id) {
            $user = User::find($comment->user_id);
            if ($user) {
                $bannedUntil = null;
                // Áp dụng khoảng thời gian cấm dựa trên lựa chọn từ SweetAlert
                if ($penalty === '1') {
                    $bannedUntil = now()->addDay();
                    $durationText = '1 ngày';
                } elseif ($penalty === '3') {
                    $bannedUntil = now()->addDays(3);
                    $durationText = '3 ngày';
                } elseif ($penalty === 'permanent') {
                    $bannedUntil = now()->addYears(100);
                    $durationText = 'vĩnh viễn';
                }
                
                // Lưu thời hạn cấm vào cơ sở dữ liệu
                if ($bannedUntil) {
                    $user->update(['comment_banned_until' => $bannedUntil]);
                }
            }
        }

        // Gửi thông báo vi phạm bình luận video và thông báo cấm tài khoản (nếu có) đến người dùng
        if ($comment->user) {
            // Thông báo nội dung bị xóa do vi phạm tiêu chuẩn cộng đồng
            app(NotificationService::class)->createForUser($comment->user, [
                'type' => 'comment.deleted',
                'title' => 'Bình luận đã bị gỡ bỏ',
                'content' => 'Bình luận của bạn trên video đã bị xóa do vi phạm tiêu chuẩn cộng đồng.',
                'action_url' => '#',
                'data' => [
                    'video_id' => $comment->video_id,
                ],
            ]);
            
            // Gửi thêm thông báo cấm nếu có áp dụng hình phạt
            if ($durationText && isset($bannedUntil)) {
                app(NotificationService::class)->createForUser($comment->user, [
                    'type' => 'user.banned',
                    'title' => 'Tài khoản bị hạn chế',
                    'content' => 'Tài khoản của bạn đã bị cấm bình luận/đánh giá ' . $durationText . ' do vi phạm chính sách cộng đồng.',
                    'action_url' => '#',
                    'data' => [
                        'banned_until' => $bannedUntil->toIso8601String(),
                    ],
                ]);
            }
        }

        // Xóa các bình luận (reply) con liên kết với bình luận cha này trước
        VideoComment::where('parent_id', $comment->id)->delete();

        // Xóa bản ghi bình luận cha
        $comment->delete();

        $msg = 'Đã xóa bình luận video thành công.';
        if ($durationText) {
            $msg .= ' Đồng thời đã cấm tài khoản viết bình luận này ' . $durationText . '.';
        }

        return back()->with('success', $msg);
    }

    public function replyReview(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ], [
            'content.required' => 'Nội dung phản hồi không được để trống.',
        ]);

        $parent = Review::findOrFail($id);

        Review::create([
            'product_id' => $parent->product_id,
            'rating' => 5, // Mặc định cho reply của admin
            'content' => $request->content,
            'parent_id' => $parent->id,
            'user_id' => Auth::id(),
            'is_approved' => 1,
        ]);

        return back()->with('success', 'Phản hồi đánh giá thành công.');
    }

    public function replyVideoComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ], [
            'content.required' => 'Nội dung phản hồi không được để trống.',
        ]);

        $parent = VideoComment::findOrFail($id);

        VideoComment::create([
            'video_id' => $parent->video_id,
            'parent_id' => $parent->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'is_approved' => 1,
        ]);

        return back()->with('success', 'Phản hồi bình luận video thành công.');
    }

    public function approveReview($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => 1]);
        return back()->with('success', 'Đã phê duyệt đánh giá thành công.');
    }

    public function approveVideoComment($id)
    {
        $comment = VideoComment::findOrFail($id);
        $comment->update(['is_approved' => 1]);
        return back()->with('success', 'Đã phê duyệt bình luận video thành công.');
    }

    public function clearReviewReports($id)
    {
        $review = Review::findOrFail($id);
        $review->update([
            'report_count' => 0,
            'is_approved' => 1
        ]);
        return back()->with('success', 'Đã gỡ bỏ báo cáo và duyệt lại đánh giá.');
    }

    public function clearVideoCommentReports($id)
    {
        $comment = VideoComment::findOrFail($id);
        $comment->update([
            'report_count' => 0,
            'is_approved' => 1
        ]);
        return back()->with('success', 'Đã gỡ bỏ báo cáo và duyệt lại bình luận.');
    }

    public function bulkDeleteReviews(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('success', 'Chưa chọn đánh giá nào để xóa.');
        }

        // Xóa replies trước, rồi xóa cha
        Review::whereIn('parent_id', $ids)->delete();
        Review::whereIn('id', $ids)->delete();

        return back()->with('success', 'Đã xóa ' . count($ids) . ' đánh giá thành công.');
    }

    public function bulkDeleteVideoComments(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('success', 'Chưa chọn bình luận nào để xóa.');
        }

        VideoComment::whereIn('parent_id', $ids)->delete();
        VideoComment::whereIn('id', $ids)->delete();

        return back()->with('success', 'Đã xóa ' . count($ids) . ' bình luận thành công.');
    }

    /**
     * Gỡ bỏ hình phạt cấm bình luận/đánh giá cho người dùng.
     * 
     * @param int $id ID của người dùng cần gỡ cấm.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unbanUser($id)
    {
        // Tìm người dùng hoặc trả về lỗi 404
        $user = User::findOrFail($id);
        
        // Reset thời hạn cấm về null
        $user->update(['comment_banned_until' => null]);
        
        // Gửi thông báo khôi phục quyền hoạt động cho người dùng
        app(NotificationService::class)->createForUser($user, [
            'type' => 'user.unbanned',
            'title' => 'Khôi phục quyền bình luận',
            'content' => 'Quyền bình luận và đánh giá của bạn đã được khôi phục.',
            'action_url' => '#',
            'data' => [],
        ]);
        
        return back()->with('success', 'Đã gỡ cấm bình luận cho người dùng ' . ($user->full_name ?? $user->name) . ' thành công.');
    }
}

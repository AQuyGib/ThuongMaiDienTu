<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\VideoComment;
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

    public function destroyReview($id)
    {
        $review = Review::findOrFail($id);
        
        // Xóa các reply con trước
        Review::where('parent_id', $review->id)->delete();
        
        // Xóa đánh giá cha
        $review->delete();

        return back()->with('success', 'Đã xóa đánh giá thành công.');
    }

    public function destroyVideoComment($id)
    {
        $comment = VideoComment::findOrFail($id);

        // Xóa các reply con trước
        VideoComment::where('parent_id', $comment->id)->delete();

        // Xóa bình luận cha
        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận video thành công.');
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
}

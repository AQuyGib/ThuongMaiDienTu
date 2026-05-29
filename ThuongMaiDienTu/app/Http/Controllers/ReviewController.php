<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Gửi đánh giá hoặc phản hồi mới cho sản phẩm.
     * 
     * @param Request $request Dữ liệu đánh giá (product_id, rating, content, parent_id, media)
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Yêu cầu đăng nhập trước khi viết đánh giá/phản hồi
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi đánh giá!'
            ], 401);
        }

        // KIỂM TRA HÌNH PHẠT CẤM HOẠT ĐỘNG:
        // - Xem người dùng hiện tại có bị cấm bình luận/đánh giá không (dựa trên cột comment_banned_until).
        // - Nếu còn trong thời hạn cấm, trả về thông báo lỗi kèm thời gian mở khóa.
        $user = Auth::user();
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
            'product_id' => 'required|exists:products,product_id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:reviews,id',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,jfif,mp4,mov,avi|max:102400', // 100MB max
        ], [
            'product_id.required' => 'Mã sản phẩm không được để trống.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Số sao đánh giá tối thiểu là 1.',
            'rating.max' => 'Số sao đánh giá tối đa là 5.',
            'content.required' => 'Nội dung đánh giá không được để trống.',
            'parent_id.exists' => 'Bình luận phản hồi không tồn tại.',
            'media.*.file' => 'Tệp tải lên không hợp lệ.',
            'media.*.mimes' => 'Định dạng file không hỗ trợ. Vui lòng chọn ảnh (jpg, jpeg, png, webp, gif, jfif) hoặc video (mp4, mov, avi).',
            'media.*.max' => 'Kích thước file vượt quá giới hạn tối đa 100MB.',
        ]);

        // Kiểm tra số lượng ảnh tối đa là 5 và kích thước tệp (ảnh <= 5MB, video <= 100MB)
        if ($request->hasFile('media')) {
            $mediaFiles = $request->file('media');
            $imageCount = 0;
            foreach ($mediaFiles as $file) {
                $mime = $file->getMimeType();
                $size = $file->getSize(); // in bytes
                
                if (str_starts_with($mime, 'image/')) {
                    $imageCount++;
                    if ($size > 5 * 1024 * 1024) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kích thước ảnh "' . $file->getClientOriginalName() . '" vượt quá giới hạn 5MB.'
                        ], 422);
                    }
                } elseif (str_starts_with($mime, 'video/')) {
                    if ($size > 100 * 1024 * 1024) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kích thước video "' . $file->getClientOriginalName() . '" vượt quá giới hạn 100MB.'
                        ], 422);
                    }
                }
            }
            if ($imageCount > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được phép tải lên tối đa 5 hình ảnh.'
                ], 422);
            }
        }

        $data = $request->only(['product_id', 'rating', 'content', 'parent_id']);
        $data['user_id'] = Auth::id();
        $data['author_name'] = null;

        // Xử lý upload media
        if ($request->hasFile('media')) {
            $mediaPaths = [];
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public');
                $mediaPaths[] = asset('storage/' . $path);
            }
            $data['media'] = $mediaPaths;
        }

        $isAdmin = (Auth::check() && in_array(Auth::user()->role_id, [1, 2]));
        if ($isAdmin) {
            $data['is_approved'] = 1;
        } else {
            $moderator = app(\App\Services\CommentModerationService::class);
            $data['is_approved'] = $moderator->isSafe($request->content) ? 1 : 0;
        }

        Review::create($data);

        return response()->json([
            'success' => true,
            'message' => $data['is_approved'] ? 'Cảm ơn bạn đã gửi đánh giá!' : 'Đánh giá của bạn chứa từ khóa nhạy cảm hoặc liên kết và đang chờ ban quản trị kiểm duyệt!'
        ]);
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Chỉ admin hoặc người sở hữu mới được xóa (tùy chính sách, ở đây cho admin/manager)
        if (Auth::check() && in_array(Auth::user()->role_id, [1, 2])) {
            $review->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đánh giá.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.'
        ], 403);
    }

    /**
     * Báo cáo đánh giá sản phẩm vi phạm.
     * - Khi nhận được báo cáo từ người dùng, tăng số lượt báo cáo `report_count`.
     * - Nếu tổng số báo cáo của đánh giá này đạt ngưỡng >= 3, đánh giá sẽ tự động bị ẩn (đặt `is_approved` về 0) chờ kiểm duyệt của Admin.
     * 
     * @param Request $request
     * @param int $id ID của đánh giá cần báo cáo.
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        // Tăng bộ đếm lượt báo cáo vi phạm
        $review->increment('report_count');

        // Ngưỡng ẩn tự động khi đạt từ 3 báo cáo trở lên
        if ($review->report_count >= 3) {
            $review->update(['is_approved' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã báo cáo vi phạm. Ban quản trị sẽ sớm xem xét đánh giá này!'
        ]);
    }
}

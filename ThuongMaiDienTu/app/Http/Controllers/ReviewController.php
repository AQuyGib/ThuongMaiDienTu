<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:reviews,id',
            'author_name' => 'nullable|string|max:255',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:5120', // 5MB max
        ]);

        $data = $request->only(['product_id', 'rating', 'content', 'parent_id', 'author_name']);
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            // Nếu đã đăng nhập thì không cần author_name từ request
            $data['author_name'] = null;
        }

        // Xử lý upload media
        if ($request->hasFile('media')) {
            $mediaPaths = [];
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public');
                $mediaPaths[] = asset('storage/' . $path);
            }
            $data['media'] = $mediaPaths;
        }

        Review::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi đánh giá!'
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
}

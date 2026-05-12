<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|string',
            'rating'      => 'required_without:parent_id|integer|min:1|max:5',
            'content'     => 'required|string',
            'media.*'     => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:51200', // max 50MB mỗi file
            'author_name' => 'nullable|string|max:255',
            'parent_id'   => 'nullable|integer|exists:reviews,id',
        ]);

        // Xử lý upload file
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public'); // storage/app/public/reviews/
                $mediaPaths[] = Storage::url($path);       // /storage/reviews/filename.ext
            }
        }

        $userId = Auth::id();
        $authorName = $userId ? Auth::user()->full_name : ($request->author_name ?? 'Khách ẩn danh');

        $review = Review::create([
            'product_id'  => $request->product_id,
            'user_id'     => $userId,
            'author_name' => $authorName,
            'parent_id'   => $request->parent_id,
            'rating'      => $request->rating ?? 5,
            'content'     => $request->content,
            'media'       => !empty($mediaPaths) ? $mediaPaths : null,
        ]);

        return response()->json([
            'success' => true,
            'review'  => $review,
            'media'   => $mediaPaths,
        ]);
    }

    public function destroy($id)
    {
        // Chỉ Admin (role_id=1) và Quản lý (role_id=2) được xóa
        $user = Auth::user();
        if (!$user || !in_array($user->role_id, [1, 2])) {
            return response()->json(['success' => false, 'message' => 'Không có quyền thực hiện hành động này.'], 403);
        }

        $review = Review::findOrFail($id);

        // Xóa file media khỏi storage nếu có
        if ($review->media) {
            foreach ($review->media as $url) {
                $path = str_replace('/storage/', '', $url);
                Storage::disk('public')->delete($path);
            }
        }

        $review->delete();

        return response()->json(['success' => true]);
    }
}

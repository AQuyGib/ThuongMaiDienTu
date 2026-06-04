<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WishlistRecentlyViewed;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'unauthenticated', 'error' => 'Vui lòng đăng nhập'], 401);
        }

        $productId = $request->input('product_id');
        $userId = Auth::id();

        $wishlist = WishlistRecentlyViewed::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('type', 'Wishlist')
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed']);
        } else {
            WishlistRecentlyViewed::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'type' => 'Wishlist',
                'created_at' => now()
            ]);
            return response()->json(['status' => 'added']);
        }
    }

    public function removeFromWishlist($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $wishlistItem = WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);
    }

    public function clearWishlist()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('type', 'Wishlist')
            ->delete();

        return response()->json(['success' => true]);
    }
}

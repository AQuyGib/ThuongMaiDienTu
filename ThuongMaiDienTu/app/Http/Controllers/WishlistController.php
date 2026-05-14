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
}

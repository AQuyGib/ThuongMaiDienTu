<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WishlistRecentlyViewed;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $userId = Auth::id();
        $productId = $request->product_id;

        $wishlist = WishlistRecentlyViewed::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('type', 'wishlist')
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed']);
        } else {
            WishlistRecentlyViewed::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'type' => 'wishlist',
                'viewed_at' => now()
            ]);
            return response()->json(['status' => 'added']);
        }
    }
}

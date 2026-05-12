<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
        $wishlist = $user->wishlists()->where('type', 'wishlist')->with('product')->get();
        
        return view('frontend.profile', compact('user', 'wishlist'));
    }

    /**
     * Update the user's profile information.
     */


    public function removeFromWishlist($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $wishlistItem = \App\Models\WishlistRecentlyViewed::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Không tìm thấy sản phẩm'], 404);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string'
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'content' => $request->content
        ]);

        return response()->json([
            'success' => true,
            'review' => $review
        ]);
    }
}

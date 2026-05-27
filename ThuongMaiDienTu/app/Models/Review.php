<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['product_id', 'user_id', 'author_name', 'parent_id', 'rating', 'content', 'media', 'is_approved', 'report_count'];

    protected $casts = [
        'media' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (Review $review) {
            if ($review->user) {
                app(NotificationService::class)->createForUser($review->user, [
                    'type' => 'review.created',
                    'title' => 'Bạn vừa gửi đánh giá',
                    'content' => 'Đánh giá của bạn cho sản phẩm #' . $review->product_id . ' đã được ghi nhận.',
                    'action_url' => url('/product/' . $review->product_id),
                    'data' => [
                        'review_id' => $review->id,
                        'product_id' => $review->product_id,
                        'rating' => $review->rating,
                    ],
                ]);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->with('user')->orderBy('created_at', 'asc');
    }
}

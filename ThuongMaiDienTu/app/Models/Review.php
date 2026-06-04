<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use \App\Traits\HasAuditLog;

    protected $fillable = ['product_id', 'user_id', 'author_name', 'parent_id', 'rating', 'content', 'media', 'is_approved', 'report_count'];

    protected $casts = [
        'media' => 'array',
    ];

    protected static function booted(): void
    {
        // Removed notification when review is created as per user request
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

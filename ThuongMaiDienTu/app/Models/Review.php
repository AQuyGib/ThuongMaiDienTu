<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['product_id', 'user_id', 'author_name', 'parent_id', 'rating', 'content', 'media'];

    protected $casts = [
        'media' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Review::class, 'parent_id')->with('user')->orderBy('created_at', 'asc');
    }
}

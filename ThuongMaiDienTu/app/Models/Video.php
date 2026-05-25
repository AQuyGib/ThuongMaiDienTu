<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $table = 'videos';

    protected $fillable = [
        'user_id',
        'uploaded_by_admin',
        'title',
        'description',
        'video_path',
        'thumbnail_path',
        'file_size',
        'mime_type',
        'status',
        'admin_note',
        'published_at',
        'youtube_url',
        'category',
        'category_id',
        'product_id',
        'duration',
        'views',
        'likes',
    ];

    protected $casts = [
        'uploaded_by_admin' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function categoryRel()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}


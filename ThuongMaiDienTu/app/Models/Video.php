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

    protected $appends = [
        'thumbnail_url',
        'video_url',
    ];

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            if (filter_var($this->thumbnail_path, FILTER_VALIDATE_URL)) {
                return $this->thumbnail_path;
            }
            
            if (file_exists(public_path($this->thumbnail_path))) {
                return asset($this->thumbnail_path);
            }
            
            return asset('storage/' . $this->thumbnail_path);
        }

        if ($this->youtube_url) {
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/|embed/)([^"&?/ ]{11})%i', $this->youtube_url, $match)) {
                return 'https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg';
            }
        }

        return 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?auto=format&fit=crop&w=800&q=80';
    }

    public function getVideoUrlAttribute()
    {
        if ($this->video_path) {
            return route('videos.stream', $this->id);
        }
        return '';
    }

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

    public function comments()
    {
        return $this->hasMany(VideoComment::class, 'video_id', 'id')->latest();
    }
}


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
    ];

    protected $casts = [
        'uploaded_by_admin' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}

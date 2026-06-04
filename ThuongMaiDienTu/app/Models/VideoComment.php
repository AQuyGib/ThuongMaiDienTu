<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    use \App\Traits\HasAuditLog;

    protected $table = 'video_comments';

    protected $fillable = [
        'video_id',
        'parent_id',
        'user_id',
        'content',
        'is_approved',
        'report_count',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(VideoComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(VideoComment::class, 'parent_id')->oldest();
    }
}

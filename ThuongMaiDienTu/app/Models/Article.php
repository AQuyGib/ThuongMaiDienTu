<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'article_id';

    protected $fillable = [
        'title', 'slug', 'summary', 'content', 'thumbnail', 'format_type', 
        'related_ticket_id', 'author_id', 'author_type', 'status', 
        'reward_points_awarded', 'embedded_product_ids', 'published_at'
    ];

    protected $casts = [
        'embedded_product_ids' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Article $article) {
            if (! $article->slug) {
                return;
            }

            app(NotificationService::class)->notifyCustomers([
                'type' => 'article.published',
                'title' => 'Có bài viết mới: ' . $article->title,
                'content' => $article->summary ?: 'Khám phá ngay bài viết mới trên trang tin công nghệ.',
                'action_url' => url('/lifestyle/' . $article->slug),
                'data' => [
                    'article_id' => $article->article_id,
                    'slug' => $article->slug,
                ],
            ]);
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function repairTicket()
    {
        return $this->belongsTo(RepairTicket::class, 'related_ticket_id', 'ticket_id');
    }

    public function approveAndReward($points)
    {
        if ($this->author_type === 'customer' && $this->status === 'pending') {
            $this->update([
                'status' => 'approved',
                'reward_points_awarded' => $points,
                'published_at' => now(),
            ]);

            RewardPoint::create([
                'user_id' => $this->author_id,
                'points' => $points,
                'reason' => 'Thưởng điểm đóng góp bài viết: ' . $this->title,
                'type' => 'earned' 
            ]);
            
            return true;
        }
        return false;
    }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    protected static function booted()
    {
        static::saved(function ($order) {
            if ($order->user_id) {
                $user = $order->user;
                if ($user) {
                    $totalSpent = $user->orders()->where('status', 'Delivered')->sum('final_amount');
                    
                    $newTier = 'Dong';
                    if ($totalSpent >= 20000000) {
                        $newTier = 'Vang';
                    } elseif ($totalSpent >= 5000000) {
                        $newTier = 'Bac';
                    }
                    
                    // Chỉ cập nhật nếu hạng thay đổi
                    if ($user->member_tier !== $newTier) {
                        $user->member_tier = $newTier;
                        $user->save();
                    }
                }
            }
        });
    }
}
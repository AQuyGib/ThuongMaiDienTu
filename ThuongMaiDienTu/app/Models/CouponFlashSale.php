<?php

namespace App\Models;

use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class CouponFlashSale extends Model {
    protected $table = 'coupons_flash_sales';
    protected $primaryKey = 'promo_id';
    public $timestamps = false;
    protected $guarded = [];

    protected static function booted()
    {
        static::created(function (CouponFlashSale $promo) {
            $service = app(NotificationService::class);

            $title = $promo->promo_type === 'FlashSale'
                ? 'Flash Sale mới vừa lên sóng'
                : 'Coupon giảm giá mới';

            $content = $promo->promo_type === 'FlashSale'
                ? 'Đã có chương trình flash sale mới. Kiểm tra ngay để không bỏ lỡ ưu đãi.'
                : 'Đã có mã coupon mới. Hãy xem chi tiết để sử dụng ưu đãi.';

            $service->notifyCustomers([
                'type' => 'promotion.auto',
                'title' => $title,
                'content' => $content,
                'action_url' => url('/products'),
                'data' => [
                    'promo_id' => $promo->promo_id,
                    'promo_type' => $promo->promo_type,
                    'code' => $promo->code,
                    'discount_val' => $promo->discount_val,
                ],
            ]);
        });

        static::updated(function (CouponFlashSale $promo) {
            if (! $promo->wasChanged(['code', 'discount_val', 'start_time', 'end_time'])) {
                return;
            }

            app(NotificationService::class)->notifyCustomers([
                'type' => 'promotion.auto_updated',
                'title' => 'Ưu đãi vừa được cập nhật',
                'content' => 'Chương trình khuyến mãi #' . $promo->promo_id . ' vừa được cập nhật, hãy xem chi tiết ngay.',
                'action_url' => url('/products'),
                'data' => [
                    'promo_id' => $promo->promo_id,
                    'promo_type' => $promo->promo_type,
                    'code' => $promo->code,
                    'discount_val' => $promo->discount_val,
                ],
            ]);
        });
    }
}

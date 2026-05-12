<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    protected $primaryKey = 'user_id';
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $hidden = ['password_hash', 'two_factor_secret', 'two_factor_code'];
    protected $casts = [
        'two_factor_expires_at' => 'datetime',
        'is_2fa_enabled' => 'boolean',
    ];

    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'user_id');
    }

    /**
     * Optimistic Locking: Cập nhật user chỉ khi version khớp.
     * Nếu version đã thay đổi (bởi admin khác), trả về false.
     *
     * @param  int   $expectedVersion  Version mà client đang giữ
     * @param  array $attributes       Các trường cần cập nhật
     * @return bool  true nếu update thành công, false nếu bị conflict
     */
    public function optimisticUpdate(int $expectedVersion, array $attributes): bool
    {
        // Dùng WHERE version = ? để đảm bảo atomic check-and-update
        $affected = static::where($this->primaryKey, $this->getKey())
            ->where('version', $expectedVersion)
            ->update(array_merge($attributes, [
                'version' => $expectedVersion + 1,
            ]));

        if ($affected === 0) {
            return false; // Conflict: version đã bị thay đổi bởi người khác
        }

        // Đồng bộ lại model instance
        $this->fill($attributes);
        $this->version = $expectedVersion + 1;

        return true;
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function sessions() {
        return $this->hasMany(UserSession::class, 'user_id');
    }
    public function activityLogs() {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
    public function orders() {
        return $this->hasMany(Order::class, 'user_id');
    }
    public function rewardPoints() {
        return $this->hasMany(RewardPoint::class, 'user_id');
    }
    public function wishlists() {
        return $this->hasMany(WishlistRecentlyViewed::class, 'user_id');
    }
    public function articles() {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function salesOrders() {
        return $this->hasMany(Order::class, 'staff_id', 'user_id');
    }

    public function loginHistories() {
        return $this->hasMany(LoginHistory::class, 'user_id');
    }

    public function repairTickets() {
        return $this->hasMany(RepairTicket::class, 'technician_id', 'user_id');
    }

    /**
     * Kiểm tra người dùng có đang online không (trong vòng 5 phút qua)
     */
    public function isOnline()
    {
        return $this->sessions()->where('last_active', '>=', now()->subMinutes(5))->exists();
    }
}
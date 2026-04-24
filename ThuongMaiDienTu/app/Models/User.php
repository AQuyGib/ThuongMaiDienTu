<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    protected $primaryKey = 'user_id';
    const UPDATED_AT = null;
    protected $guarded = [];
    protected $hidden = ['password_hash', 'two_factor_secret'];

    public function getAuthPassword() {
        return $this->password_hash;
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
}
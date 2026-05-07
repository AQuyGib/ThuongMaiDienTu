<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_histories';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at'
    ];

    public function getDeviceDisplayAttribute()
    {
        $ua = $this->user_agent;
        $os = 'Unknown OS';
        $browser = 'Unknown Browser';

        // Get OS
        if (preg_match('/windows|win32/i', $ua)) $os = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $ua)) $os = 'Mac OS';
        elseif (preg_match('/android/i', $ua)) $os = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $ua)) $os = 'iOS';
        elseif (preg_match('/linux/i', $ua)) $os = 'Linux';

        // Get Browser
        if (preg_match('/msie|trident/i', $ua)) $browser = 'Internet Explorer';
        elseif (preg_match('/edge|edg/i', $ua)) $browser = 'Microsoft Edge';
        elseif (preg_match('/firefox/i', $ua)) $browser = 'Firefox';
        elseif (preg_match('/chrome/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/safari/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/opera|opr/i', $ua)) $browser = 'Opera';

        return "{$browser} / {$os}";
    }
}

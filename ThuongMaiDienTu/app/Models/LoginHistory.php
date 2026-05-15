<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

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
        $agent = new Agent();
        if ($this->user_agent) {
            $agent->setUserAgent($this->user_agent);
        }

        $os = $agent->platform() ?: 'Unknown OS';
        $browser = $agent->browser() ?: 'Unknown Browser';

        return "{$browser} / {$os}";
    }
}

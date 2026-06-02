<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIOrderLog extends Model
{
    protected $table = 'ai_order_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

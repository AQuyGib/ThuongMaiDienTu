<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairTicket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';
    public $timestamps = false;
    
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'service_name',
        'service_fee',
        'invoice_no',
        'invoiced_at',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'invoiced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id', 'user_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'related_ticket_id', 'ticket_id');
    }
}

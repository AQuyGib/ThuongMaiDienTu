<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairTicket extends Model
{
    use HasFactory;

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
}

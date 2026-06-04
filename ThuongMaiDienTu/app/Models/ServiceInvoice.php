<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceInvoice extends Model
{
    use HasFactory, \App\Traits\HasAuditLog;

    protected $fillable = [
        'invoice_no',
        'customer_name',
        'customer_phone',
        'customer_email',
        'imei_serial',
        'service_name',
        'description',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'issued_date',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'issued_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

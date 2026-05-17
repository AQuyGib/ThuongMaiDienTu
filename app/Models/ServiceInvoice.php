<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'customer_name',
        'customer_phone',
        'customer_email',
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
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

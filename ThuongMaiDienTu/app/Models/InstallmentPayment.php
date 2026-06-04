<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Get the installment contract that owns this payment term.
     */
    public function installment()
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }

    /**
     * Accessor for payment_no mapped to term_number database column
     */
    public function getPaymentNoAttribute()
    {
        return $this->term_number;
    }

    /**
     * Accessor for paid_at mapped to payment_date database column
     */
    public function getPaidAtAttribute()
    {
        return $this->payment_date;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ai_analysis' => 'array',
        'trade_in' => 'boolean',
    ];

    /**
     * Get the order associated with this installment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the payment schedule terms for this installment.
     */
    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_id');
    }

    /**
     * Accessor for AI Recommendation
     */
    public function getAiRecommendationAttribute()
    {
        return $this->ai_analysis['recommendation'] ?? null;
    }

    /**
     * Accessor for AI Reasoning / Reason
     */
    public function getAiReasoningAttribute()
    {
        return $this->ai_analysis['reason'] ?? null;
    }

    /**
     * Accessor for AI Findings
     */
    public function getAiFindingsAttribute()
    {
        return $this->ai_analysis['findings'] ?? [];
    }
}

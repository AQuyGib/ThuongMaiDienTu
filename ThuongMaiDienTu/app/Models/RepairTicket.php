<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairTicket extends Model
{
    use HasFactory, \App\Traits\HasAuditLog;

    protected $primaryKey = 'ticket_id';
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'technician_id',
        'imei_serial',
        'issue_desc',
        'schedule_date',
        'estimated_cost',
        'status',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_email',
        'customer_source',
        'service_name',
        'service_fee',
        'invoice_no',
        'invoiced_at',
        'device_image',
        'ai_diagnosed',
        'ai_fault_type',
        'ai_probable_causes',
        'ai_risk_warnings',
        'ai_replacement_parts',
        'ai_estimated_cost_min',
        'ai_estimated_cost_max',
        'ai_complexity_level',
        'ai_recommended_skills',
        'ai_dispatch_reason',
        'ai_diagnosed_at',
    ];

    protected $casts = [
        'service_fee' => 'integer',
        'invoiced_at' => 'datetime',
        'ai_diagnosed' => 'boolean',
        'ai_probable_causes' => 'json',
        'ai_risk_warnings' => 'json',
        'ai_recommended_skills' => 'json',
        'ai_diagnosed_at' => 'datetime',
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

    public function serviceInvoice()
    {
        return $this->belongsTo(ServiceInvoice::class, 'invoice_no', 'invoice_no');
    }
}

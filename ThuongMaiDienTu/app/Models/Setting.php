<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditLog;

class Setting extends Model {
    use HasAuditLog;

    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
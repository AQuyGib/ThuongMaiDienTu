<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
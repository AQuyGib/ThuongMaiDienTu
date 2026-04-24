<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model {
    protected $primaryKey = 'cashbook_id';
    const UPDATED_AT = null;
    protected $guarded = [];
}
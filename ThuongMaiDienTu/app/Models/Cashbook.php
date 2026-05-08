<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model {
    protected $primaryKey = 'cashbook_id';
    const UPDATED_AT = null;
    protected $guarded = [];
    
       public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $term)
    {
        if ($term) {
            return $query->where('description', 'like', '%' . $term . '%');
        }
        return $query;
    }
}
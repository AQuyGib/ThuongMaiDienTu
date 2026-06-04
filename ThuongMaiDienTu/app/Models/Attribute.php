<?php

namespace App\Models;

use App\Traits\BaseTranslationTrait;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use BaseTranslationTrait, \App\Traits\HasAuditLog;

    protected $primaryKey = 'attribute_id';
    public $timestamps = false;
    protected $guarded = [];

    protected array $translatable = [
        'name',
        'description',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }
}

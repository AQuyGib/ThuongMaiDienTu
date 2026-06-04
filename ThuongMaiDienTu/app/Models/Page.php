<?php

namespace App\Models;

use App\Traits\BaseTranslationTrait;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use BaseTranslationTrait, \App\Traits\HasAuditLog;

    protected $table = 'pages';
    protected $primaryKey = 'page_id';
    public $timestamps = false;
    protected $guarded = [];

    protected array $translatable = [
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];
}

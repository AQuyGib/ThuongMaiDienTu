<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    protected $table = 'page_translations';

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }
}

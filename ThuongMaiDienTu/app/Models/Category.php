<?php

namespace App\Models;

use App\Traits\BaseTranslationTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BaseTranslationTrait;

    protected $primaryKey = 'category_id';
    public $timestamps = false;
    protected $guarded = [];

    protected array $translatable = [
        'name',
        'description',
        'seo_description',
    ];

    protected $casts = [
        'filter_config' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

<<<<<<< HEAD
    /**
     * Kiểm tra xem một danh mục con (childId) có phải là con cháu của danh mục cha (parentId) hay không
     */
    public static function isDescendant($childId, $parentId) {
        if (empty($childId) || empty($parentId)) {
            return false;
        }
        $current = self::find($childId);
        while ($current) {
            if ((int)$current->parent_id === (int)$parentId) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    public function getRootCategoryId() {
=======
    public function getRootCategoryId()
    {
>>>>>>> master
        $rootId = $this->category_id;
        $current = $this;

        while ($current->parent_id) {
            $parent = Category::find($current->parent_id);
            if (! $parent) {
                break;
            }

            $current = $parent;
            $rootId = $current->category_id;
        }

        return $rootId;
    }
}

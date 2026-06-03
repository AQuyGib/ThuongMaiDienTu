<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TranslatesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    use TranslatesResource;

    public function toArray(Request $request): array
    {
        $locale = $request->attributes->get('locale', $request->query('locale', app()->getLocale()));

        return [
            'id' => $this->category_id,
            'parent_id' => $this->parent_id,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) ($this->is_active ?? true),
            'locale' => $locale,
            'name' => $this->translated('name', $locale),
            'description' => $this->translated('description', $locale),
            'seo_description' => $this->translated('seo_description', $locale),
            'translations' => $this->mergeTranslations($locale),
            'parent' => $this->whenLoaded('parent', function () use ($locale) {
                return $this->parent ? new self($this->parent) : null;
            }),
            'children' => $this->whenLoaded('children', function () {
                return self::collection($this->children);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

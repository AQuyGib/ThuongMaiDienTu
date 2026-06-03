<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TranslatesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use TranslatesResource;

    public function toArray(Request $request): array
    {
        $locale = $request->attributes->get('locale', $request->query('locale', app()->getLocale()));

        return [
            'id' => $this->product_id,
            'category_id' => $this->category_id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'price' => $this->base_price,
            'discount_percent' => $this->discount_percent,
            'stock' => $this->stock ?? null,
            'is_active' => (bool) ($this->is_active ?? true),
            'thumbnail' => $this->thumbnail,
            'images' => $this->images,
            'locale' => $locale,
            'name' => $this->translated('name', $locale),
            'description' => $this->translated('description', $locale),
            'seo_description' => $this->translated('seo_description', $locale),
            'translations' => $this->mergeTranslations($locale),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

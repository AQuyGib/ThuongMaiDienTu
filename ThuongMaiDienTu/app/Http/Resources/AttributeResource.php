<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TranslatesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    use TranslatesResource;

    public function toArray(Request $request): array
    {
        $locale = $request->attributes->get('locale', $request->query('locale', app()->getLocale()));

        return [
            'id' => $this->attribute_id,
            'slug' => $this->slug,
            'type' => $this->type ?? null,
            'is_active' => (bool) ($this->is_active ?? true),
            'locale' => $locale,
            'name' => $this->translated('name', $locale),
            'description' => $this->translated('description', $locale),
            'translations' => $this->mergeTranslations($locale),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

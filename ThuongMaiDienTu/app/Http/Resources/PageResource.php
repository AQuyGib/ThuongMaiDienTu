<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TranslatesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    use TranslatesResource;

    public function toArray(Request $request): array
    {
        $locale = $request->attributes->get('locale', $request->query('locale', app()->getLocale()));

        return [
            'id' => $this->page_id,
            'slug' => $this->slug,
            'is_active' => (bool) ($this->is_active ?? true),
            'locale' => $locale,
            'title' => $this->translated('title', $locale),
            'excerpt' => $this->translated('excerpt', $locale),
            'content' => $this->translated('content', $locale),
            'meta_title' => $this->translated('meta_title', $locale),
            'meta_description' => $this->translated('meta_description', $locale),
            'translations' => $this->mergeTranslations($locale),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

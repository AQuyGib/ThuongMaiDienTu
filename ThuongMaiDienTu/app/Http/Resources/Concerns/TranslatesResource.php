<?php

namespace App\Http\Resources\Concerns;

trait TranslatesResource
{
    protected function mergeTranslations(?string $locale = null): array
    {
        $resource = $this->resource ?? null;

        if (! $resource || ! method_exists($resource, 'translatedAttributes')) {
            return [];
        }

        return $resource->translatedAttributes($locale);
    }

    protected function translated(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        $resource = $this->resource ?? null;

        if (! $resource || ! method_exists($resource, 'translated')) {
            return $default;
        }

        return $resource->translated($key, $locale, $default);
    }
}

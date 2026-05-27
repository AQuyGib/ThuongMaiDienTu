<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiWrapsResponse
{
    protected function withApiMeta(array $meta = [], ?string $message = null): array
    {
        return [
            'meta' => $meta,
            'message' => $message,
            'locale' => request()->attributes->get('locale', app()->getLocale()),
        ];
    }

    protected static function wrapCollection(AnonymousResourceCollection $collection, array $meta = [], ?string $message = null): AnonymousResourceCollection
    {
        return $collection->additional([
            'meta' => $meta,
            'message' => $message,
            'locale' => request()->attributes->get('locale', app()->getLocale()),
        ]);
    }

    protected static function wrapResource(JsonResource $resource, array $meta = [], ?string $message = null): JsonResource
    {
        return $resource->additional([
            'meta' => $meta,
            'message' => $message,
            'locale' => request()->attributes->get('locale', app()->getLocale()),
        ]);
    }
}

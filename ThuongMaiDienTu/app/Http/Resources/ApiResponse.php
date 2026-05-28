<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->resource,
            'locale' => $request->attributes->get('locale', app()->getLocale()),
            'meta' => [],
            'message' => null,
        ];
    }

    public static function success(mixed $data, array $meta = [], ?string $message = null): array
    {
        return [
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
        ];
    }
}

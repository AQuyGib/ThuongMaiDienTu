<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

if (! function_exists('translatable')) {
    /**
     * Helper render nhanh cho Blade/API.
     *
     * Ví dụ:
     * translatable($product, 'name')
     * translatable($product, 'description', 'en')
     * translatable($product, null) => trả về mảng field dịch hiện tại
     */
    function translatable(Model|Arrayable|array|null $model, ?string $key = null, ?string $locale = null, mixed $default = null): mixed
    {
        if ($model === null) {
            return $default;
        }

        $payload = null;

        if ($model instanceof Model && method_exists($model, 'translated')) {
            if ($key !== null) {
                return $model->translated($key, $locale, $default);
            }

            return method_exists($model, 'translatedAttributes')
                ? $model->translatedAttributes($locale)
                : $default;
        }

        if ($model instanceof Arrayable) {
            $payload = $model->toArray();
        } elseif (is_array($model)) {
            $payload = $model;
        }

        if ($payload === null) {
            return $default;
        }

        if ($key !== null) {
            return data_get($payload, $key, $default);
        }

        return $payload;
    }
}

if (! function_exists('translatable_attributes')) {
    /**
     * Trả về toàn bộ dữ liệu đã dịch của model.
     *
     * Ví dụ:
     * translatable_attributes($product)
     */
    function translatable_attributes(Model|Arrayable|array|null $model, ?string $locale = null): array
    {
        if ($model === null) {
            return [];
        }

        if ($model instanceof Model && method_exists($model, 'translatedAttributes')) {
            return $model->translatedAttributes($locale);
        }

        if ($model instanceof Arrayable) {
            return $model->toArray();
        }

        return is_array($model) ? $model : [];
    }
}

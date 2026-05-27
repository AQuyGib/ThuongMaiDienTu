<?php

namespace App\Traits;

use App\Jobs\TranslateModelJob;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait BaseTranslationTrait
{
    public static function bootBaseTranslationTrait(): void
    {
        static::saved(function ($model) {
            if (! $model->shouldAutoTranslate()) {
                return;
            }

            if ($model->shouldSkipTranslationSync()) {
                return;
            }

            if ($model->shouldQueueTranslation()) {
                TranslateModelJob::dispatch(
                    $model::class,
                    $model->getKey(),
                    $model->getTargetLocale()
                );

                return;
            }

            $model->syncTranslations();
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(
            $this->getTranslationModelClass(),
            $this->getTranslationForeignKey()
        );
    }

    /**
     * Tự eager load translation phù hợp locale hiện tại để tránh N+1 query.
     */
    public function scopeWithTranslation(Builder $query, ?string $locale = null): Builder
    {
        return $query->with([
            'translations' => function ($relation) use ($locale) {
                $relation->where('locale', $this->resolveLocaleForQuery($locale));
            },
        ]);
    }

    /**
     * Query nhanh các model đang có translation ở locale hiện tại.
     */
    public function scopeTranslated(Builder $query, ?string $locale = null): Builder
    {
        return $query->whereHas('translations', function ($relation) use ($locale) {
            $relation->where('locale', $this->resolveLocaleForQuery($locale));
        });
    }

    public function translation(?string $locale = null)
    {
        $locale = $this->resolveLocaleForQuery($locale);

        if ($this->relationLoaded('translations')) {
            $cached = $this->translations->firstWhere('locale', $locale);
            if ($cached) {
                return $cached;
            }
        }

        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', $this->getFallbackLocale())->first();
    }

    public function translateTo(string $locale): array
    {
        $translation = $this->translation($locale);

        return collect($this->getTranslatableAttributes())
            ->mapWithKeys(function (string $attribute) use ($translation) {
                return [$attribute => $translation?->$attribute ?? $this->getAttributeValue($attribute)];
            })
            ->all();
    }

    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            $translation = $this->translation();

            return $translation?->$key ?? $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    public function syncTranslations(): void
    {
        $attributes = $this->getTranslatableAttributes();

        if (empty($attributes) || ! $this->exists) {
            return;
        }

        $sourceLocale = $this->getSourceLocale();
        $targetLocale = $this->getTargetLocale();

        if ($sourceLocale === $targetLocale) {
            return;
        }

        $payload = [];
        foreach ($attributes as $attribute) {
            $payload[$attribute] = $this->getRawOriginal($attribute) ?? $this->getAttributeValue($attribute);
        }

        $translatedPayload = $this->translatePayload($payload, $sourceLocale, $targetLocale);
        $translationModel = $this->getTranslationModelClass();

        $translationModel::updateOrCreate(
            [
                $this->getTranslationForeignKey() => $this->getKey(),
                'locale' => $targetLocale,
            ],
            array_merge([
                $this->getTranslationForeignKey() => $this->getKey(),
                'locale' => $targetLocale,
            ], $translatedPayload)
        );
    }

    protected function translatePayload(array $payload, string $sourceLocale, string $targetLocale): array
    {
        if ($sourceLocale === $targetLocale) {
            return $payload;
        }

        /** @var TranslationService $service */
        $service = app(TranslationService::class);

        $result = [];

        foreach ($payload as $attribute => $value) {
            $result[$attribute] = is_string($value) && trim($value) !== ''
                ? $service->translate($value, $sourceLocale, $targetLocale)
                : $value;
        }

        return $result;
    }

    public function getTranslatableAttributes(): array
    {
        if (! property_exists($this, 'translatable')) {
            return [];
        }

        return array_values(array_unique(array_filter($this->translatable)));
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes(), true);
    }

    public function getSourceLocale(): string
    {
        return (string) config('translatable.source_locale', 'vi');
    }

    public function getTargetLocale(): string
    {
        return (string) config('translatable.default_target_locale', 'en');
    }

    public function getFallbackLocale(): string
    {
        return (string) config('translatable.fallback_locale', config('app.fallback_locale', 'vi'));
    }

    public function resolveLocaleForQuery(?string $locale = null): string
    {
        return $locale ?: App::getLocale();
    }

    public function getCurrentTranslation(): mixed
    {
        return $this->translation();
    }

    public function shouldAutoTranslate(): bool
    {
        return (bool) config('translatable.auto_translate', true);
    }

    public function shouldQueueTranslation(): bool
    {
        return (bool) config('translatable.observer.queue_if_available', false);
    }

    public function shouldSkipTranslationSync(): bool
    {
        if (! (bool) config('translatable.observer.sync_only_dirty', true)) {
            return false;
        }

        if (! $this->exists) {
            return false;
        }

        foreach ($this->getTranslatableAttributes() as $attribute) {
            if ($this->wasChanged($attribute)) {
                return false;
            }
        }

        return true;
    }

    public function isSourceLocaleData(): bool
    {
        return true;
    }

    public function getTranslationForeignKey(): string
    {
        return Str::snake(class_basename($this)) . '_id';
    }

    public function getTranslationModelClass(): string
    {
        $candidate = app()->getNamespace() . 'Models\\' . class_basename($this) . config('translatable.translation_suffix', 'Translation');

        return $candidate;
    }

    public function getTranslationTableName(): string
    {
        return Str::snake(class_basename($this)) . config('translatable.translation_table_suffix', '_translations');
    }

    /**
     * Helper render nhanh cho Blade/API Resource.
     * Dùng khi muốn lấy riêng một field dịch theo locale hiện tại hoặc locale chỉ định.
     */
    public function translated(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return $default ?? $this->getAttributeValue($key);
        }

        $translation = $this->translation($locale);

        return data_get($translation, $key, $default ?? $this->getAttributeValue($key));
    }

    /**
     * Helper render nhanh toàn bộ payload cho Blade/API Resource.
     * Có thể merge vào array response mà không cần gọi từng field thủ công.
     */
    public function translatedAttributes(?string $locale = null): array
    {
        return $this->translateTo($locale ?: App::getLocale());
    }

    /**
     * Helper dùng trực tiếp trong Blade để tránh lặp code.
     * Ví dụ: {{ $product->t('name') }}
     */
    public function t(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        return $this->translated($key, $locale, $default);
    }
}

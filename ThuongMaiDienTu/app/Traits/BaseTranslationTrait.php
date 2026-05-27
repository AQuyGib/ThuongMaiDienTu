<?php

namespace App\Traits;

use App\Jobs\TranslateModelJob;
use App\Services\TranslationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Trait dùng chung cho các Eloquent Model cần đa ngôn ngữ hóa dữ liệu động
 * (như Tên sản phẩm, Tên danh mục, Mô tả...).
 * 
 * Tự động bắt sự kiện `saved` để dịch dữ liệu sang Tiếng Anh thông qua API Google Translate,
 * lưu trữ tại bảng dịch riêng biệt và tự động nạp bản dịch khi truy cập thuộc tính Model.
 */
trait BaseTranslationTrait
{
    /**
     * Khởi động Trait tự động (Boot method của Laravel Eloquent).
     * Đăng ký bộ lắng nghe sự kiện `saved` của Model.
     */
    public static function bootBaseTranslationTrait(): void
    {
        static::saved(function ($model) {
            // Kiểm tra xem hệ thống có bật chế độ tự động dịch hay không
            if (! $model->shouldAutoTranslate()) {
                return;
            }

            // Bỏ qua nếu không có trường translatable nào thay đổi
            if ($model->shouldSkipTranslationSync()) {
                return;
            }

            // Nếu cấu hình chạy qua hàng đợi (Queue), dispatch Job để chạy bất đồng bộ
            if ($model->shouldQueueTranslation()) {
                TranslateModelJob::dispatch(
                    $model::class,
                    $model->getKey(),
                    $model->getTargetLocale()
                );

                return;
            }

            // Ngược lại, thực hiện dịch đồng bộ ngay lập tức
            $model->syncTranslations();
        });
    }

    /**
     * Mối quan hệ hasMany trỏ đến bảng dịch tương ứng (Ví dụ: Product -> ProductTranslation).
     */
    public function translations(): HasMany
    {
        return $this->hasMany(
            $this->getTranslationModelClass(),
            $this->getTranslationForeignKey()
        );
    }

    /**
     * Scope hỗ trợ Eager Loading bản dịch khớp với Locale chỉ định (hoặc mặc định)
     * giúp tối ưu hiệu năng, tránh truy vấn N+1.
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
     * Scope lọc ra những model đã có bản dịch ở Locale chỉ định.
     */
    public function scopeTranslated(Builder $query, ?string $locale = null): Builder
    {
        return $query->whereHas('translations', function ($relation) use ($locale) {
            $relation->where('locale', $this->resolveLocaleForQuery($locale));
        });
    }

    /**
     * Lấy bản dịch tương ứng của locale chỉ định (hoặc mặc định của ứng dụng).
     */
    public function translation(?string $locale = null)
    {
        $locale = $this->resolveLocaleForQuery($locale);

        // Ưu tiên lấy từ Eager loaded relationship để tránh query SQL thừa
        if ($this->relationLoaded('translations')) {
            $cached = $this->translations->firstWhere('locale', $locale);
            if ($cached) {
                return $cached;
            }
        }

        // Truy vấn database tìm bản dịch, nếu không có sẽ lấy theo ngôn ngữ gốc làm fallback
        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', $this->getFallbackLocale())->first();
    }

    /**
     * Lấy mảng dữ liệu đã được dịch đầy đủ cho các trường đa ngôn ngữ.
     */
    public function translateTo(string $locale): array
    {
        $translation = $this->translation($locale);

        return collect($this->getTranslatableAttributes())
            ->mapWithKeys(function (string $attribute) use ($translation) {
                return [$attribute => $translation?->$attribute ?? $this->getAttributeValue($attribute)];
            })
            ->all();
    }

    /**
     * Chặn phương thức `getAttribute` mặc định của Eloquent:
     * Nếu trường đang truy cập nằm trong danh sách trường cần dịch, tự động trả về bản dịch tương ứng.
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            $translation = $this->translation();

            // Nếu tồn tại bản dịch thì trả về bản dịch, ngược lại fallback về giá trị gốc của cột chính
            return $translation?->$key ?? $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Đồng bộ hóa bản dịch: Dịch các trường văn bản và cập nhật/tạo mới vào bảng dịch.
     */
    public function syncTranslations(): void
    {
        $attributes = $this->getTranslatableAttributes();

        if (empty($attributes) || ! $this->exists) {
            return;
        }

        $sourceLocale = $this->getSourceLocale();
        $targetLocale = $this->getTargetLocale();

        // Không cần dịch nếu ngôn ngữ đích trùng ngôn ngữ nguồn
        if ($sourceLocale === $targetLocale) {
            return;
        }

        // Thu thập payload các giá trị gốc cần mang đi dịch
        $payload = [];
        foreach ($attributes as $attribute) {
            $payload[$attribute] = $this->getRawOriginal($attribute) ?? $this->getAttributeValue($attribute);
        }

        // Gọi hàm dịch payload
        $translatedPayload = $this->translatePayload($payload, $sourceLocale, $targetLocale);
        $translationModel = $this->getTranslationModelClass();

        // Lưu bản dịch vào bảng dịch (Ví dụ: product_translations)
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

    /**
     * Dịch toàn bộ payload dữ liệu qua TranslationService.
     */
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

    /**
     * Lấy danh sách các trường cần dịch được khai báo tại Model (mảng $translatable).
     */
    public function getTranslatableAttributes(): array
    {
        if (! property_exists($this, 'translatable')) {
            return [];
        }

        return array_values(array_unique(array_filter($this->translatable)));
    }

    /**
     * Kiểm tra xem thuộc tính cụ thể có phải là trường đa ngôn ngữ hay không.
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes(), true);
    }

    /**
     * Lấy ngôn ngữ nguồn cấu hình từ hệ thống (mặc định: 'vi').
     */
    public function getSourceLocale(): string
    {
        return (string) config('translatable.source_locale', 'vi');
    }

    /**
     * Lấy ngôn ngữ dịch đích mặc định (mặc định: 'en').
     */
    public function getTargetLocale(): string
    {
        return (string) config('translatable.default_target_locale', 'en');
    }

    /**
     * Lấy ngôn ngữ fallback dự phòng.
     */
    public function getFallbackLocale(): string
    {
        return (string) config('translatable.fallback_locale', config('app.fallback_locale', 'vi'));
    }

    /**
     * Phân giải locale hiện tại dùng cho câu truy vấn.
     */
    public function resolveLocaleForQuery(?string $locale = null): string
    {
        return $locale ?: App::getLocale();
    }

    /**
     * Lấy bản dịch hiện tại.
     */
    public function getCurrentTranslation(): mixed
    {
        return $this->translation();
    }

    /**
     * Kiểm tra cấu hình có bật tính năng tự động dịch hay không.
     */
    public function shouldAutoTranslate(): bool
    {
        return (bool) config('translatable.auto_translate', true);
    }

    /**
     * Kiểm tra cấu hình có cho phép xếp hàng dịch vào queue hay không.
     */
    public function shouldQueueTranslation(): bool
    {
        return (bool) config('translatable.observer.queue_if_available', false);
    }

    /**
     * Kiểm tra tối ưu: Chỉ dịch lại khi có ít nhất một trường đa ngôn ngữ bị thay đổi dữ liệu (Dirty).
     */
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

    /**
     * Tự động suy luận khóa ngoại của bảng dịch (Ví dụ: Product -> product_id).
     */
    public function getTranslationForeignKey(): string
    {
        return Str::snake(class_basename($this)) . '_id';
    }

    /**
     * Tự động suy luận tên lớp Model dịch (Ví dụ: Product -> App\Models\ProductTranslation).
     */
    public function getTranslationModelClass(): string
    {
        $candidate = app()->getNamespace() . 'Models\\' . class_basename($this) . config('translatable.translation_suffix', 'Translation');

        return $candidate;
    }

    /**
     * Tự động suy luận tên bảng dịch trong database (Ví dụ: Product -> product_translations).
     */
    public function getTranslationTableName(): string
    {
        return Str::snake(class_basename($this)) . config('translatable.translation_table_suffix', '_translations');
    }

    /**
     * Helper render nhanh giá trị dịch cho Blade/API Resource.
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
     * Helper lấy toàn bộ các thuộc tính dịch dưới dạng mảng key => value.
     */
    public function translatedAttributes(?string $locale = null): array
    {
        return $this->translateTo($locale ?: App::getLocale());
    }

    /**
     * Helper rút gọn tối đa dùng trong file Blade view.
     * Cú pháp sử dụng: {{ $product->t('name') }}
     */
    public function t(string $key, ?string $locale = null, mixed $default = null): mixed
    {
        return $this->translated($key, $locale, $default);
    }
}

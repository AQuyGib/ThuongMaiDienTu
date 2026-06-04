<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * BaseTranslationObserver chuẩn enterprise.
 *
 * Vai trò:
 * - Tách riêng vòng đời observer khỏi trait.
 * - Cho phép áp dụng đồng thời theo 2 kiểu:
 *   1) Trait boot method: tự động dùng ngay khi model use BaseTranslationTrait
 *   2) Observer: đăng ký thủ công trong AppServiceProvider cho các model cần kiểm soát chặt
 *
 * Khuyến nghị:
 * - Nếu dự án cần đồng nhất và ít cấu hình, chỉ cần trait.
 * - Nếu dự án lớn có nhiều rule nghiệp vụ theo từng model, dùng observer để dễ kiểm soát.
 */
class BaseTranslationObserver
{
    public function created(Model $model): void
    {
        $this->sync($model);
    }

    public function updated(Model $model): void
    {
        $this->sync($model);
    }

    protected function sync(Model $model): void
    {
        if (! method_exists($model, 'shouldAutoTranslate') || ! method_exists($model, 'syncTranslations')) {
            return;
        }

        if (! $model->shouldAutoTranslate()) {
            return;
        }

        if (method_exists($model, 'shouldSkipTranslationSync') && $model->shouldSkipTranslationSync()) {
            return;
        }

        $model->syncTranslations();
    }
}

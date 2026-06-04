<?php

namespace App\Traits;

use App\Jobs\LogAuditEventJob;
use App\Services\AuditMasker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    /**
     * Tự động đăng ký các trình lắng nghe sự kiện của Model
     */
    public static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            self::dispatchAuditJob('created', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            if ($model->isDirty()) {
                $dirtyKeys = array_keys($model->getDirty());
                $oldValues = array_intersect_key($model->getRawOriginal(), array_flip($dirtyKeys));
                $newValues = $model->getDirty();

                self::dispatchAuditJob('updated', $model, $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            self::dispatchAuditJob('deleted', $model, $model->getRawOriginal(), null);
        });

        // Đăng ký động sự kiện của SoftDeletes nếu model sử dụng SoftDeletes
        if (method_exists(static::class, 'bootSoftDeletes')) {
            static::restored(function (Model $model) {
                self::dispatchAuditJob('restored', $model, null, $model->getAttributes());
            });

            static::forceDeleted(function (Model $model) {
                self::dispatchAuditJob('deleted', $model, $model->getRawOriginal(), null);
            });
        }
    }

    /**
     * Chuẩn bị dữ liệu và đẩy Job ghi log vào hàng đợi
     */
    protected static function dispatchAuditJob(string $event, Model $model, ?array $old, ?array $new): void
    {
        // 1. Áp dụng Masking dữ liệu nhạy cảm
        $maskedOld = $old ? AuditMasker::mask($old) : null;
        $maskedNew = $new ? AuditMasker::mask($new) : null;

        // 2. Thu thập thông tin Causer (Người thao tác)
        $causer = Auth::user();
        
        $payload = [
            'event' => $event,
            'causer_type' => $causer ? get_class($causer) : 'System',
            'causer_id' => $causer ? $causer->user_id : 0,
            'causer_name' => $causer ? $causer->full_name : 'System Scheduler',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'old_values' => $maskedOld ? json_encode($maskedOld, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $maskedNew ? json_encode($maskedNew, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toDateTimeString(),
        ];

        // 3. Dispatch Job chạy ngầm trên queue mặc định (để worker default xử lý được ngay)
        LogAuditEventJob::dispatch($payload);
    }

    /**
     * Ghi log thủ công cho sự kiện tùy chỉnh (như login, export,...)
     */
    public static function logManualEvent(string $event, ?string $subjectType = null, ?int $subjectId = null, ?array $old = null, ?array $new = null, ?string $causerOverrideName = null): void
    {
        // Thu thập thông tin Causer (Người thao tác)
        $causer = \Illuminate\Support\Facades\Auth::user();
        
        $payload = [
            'event' => $event,
            'causer_type' => $causer ? get_class($causer) : 'System',
            'causer_id' => $causer ? $causer->user_id : 0,
            'causer_name' => $causerOverrideName ?: ($causer ? $causer->full_name : 'System Scheduler'),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $old ? json_encode(AuditMasker::mask($old), JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $new ? json_encode(AuditMasker::mask($new), JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()->toDateTimeString(),
        ];

        LogAuditEventJob::dispatch($payload);
    }
}

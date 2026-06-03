<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class TranslationSyncService
{
    public function sync(Model $model, array $payload = [], ?string $targetLocale = null): void
    {
        if (! method_exists($model, 'syncTranslations')) {
            return;
        }

        $model->syncTranslations();
    }
}

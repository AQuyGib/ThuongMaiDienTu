<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $modelClass,
        protected int|string $modelKey,
        protected string $targetLocale
    ) {
        $this->onQueue(config('translatable.queue.name', 'translations'));
    }

    public function handle(TranslationService $translationService): void
    {
        if (! class_exists($this->modelClass)) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->modelClass::query()->find($this->modelKey);

        if (! $model || ! method_exists($model, 'syncTranslations')) {
            return;
        }

        try {
            $model->syncTranslations();
        } catch (\Throwable $e) {
            Log::error('TranslateModelJob failed', [
                'modelClass' => $this->modelClass,
                'modelKey' => $this->modelKey,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

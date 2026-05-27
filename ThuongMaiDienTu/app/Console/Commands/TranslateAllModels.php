<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TranslateAllModels extends Command
{
    protected $signature = 'translate:all
        {--model= : Specific model class (e.g. Product, Category)}
        {--force : Re-translate even if translation already exists}';

    protected $description = 'Batch translate all translatable models to target locale';

    public function handle(): int
    {
        $models = config('translatable.models', [
            \App\Models\Product::class,
            \App\Models\Category::class,
            \App\Models\Attribute::class,
            \App\Models\Page::class,
        ]);

        $specificModel = $this->option('model');
        $force = $this->option('force');

        if ($specificModel) {
            $fullClass = str_contains($specificModel, '\\')
                ? $specificModel
                : 'App\\Models\\' . $specificModel;

            if (! class_exists($fullClass)) {
                $this->error("Model class [{$fullClass}] not found.");
                return 1;
            }

            $models = [$fullClass];
        }

        foreach ($models as $modelClass) {
            if (! class_exists($modelClass)) {
                $this->warn("Skipping [{$modelClass}] – class not found.");
                continue;
            }

            $this->info("Translating: {$modelClass}");

            $query = $modelClass::query();

            if (! $force) {
                $query->whereDoesntHave('translations', function ($q) {
                    $q->where('locale', config('translatable.default_target_locale', 'en'));
                });
            }

            $total = $query->count();

            if ($total === 0) {
                $this->info("  → No records need translating.");
                continue;
            }

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $query->chunkById(10, function ($records) use ($bar) {
                foreach ($records as $record) {
                    try {
                        $record->syncTranslations();
                    } catch (\Throwable $e) {
                        $this->newLine();
                        $this->warn("  Error translating #{$record->getKey()}: {$e->getMessage()}");
                    }
                    $bar->advance();
                }

                // Small delay to avoid rate limiting on free API
                usleep(500000); // 0.5s
            });

            $bar->finish();
            $this->newLine();
            $this->info("  → Done ({$total} records).");
        }

        $this->newLine();
        $this->info('All translations completed!');

        return 0;
    }
}

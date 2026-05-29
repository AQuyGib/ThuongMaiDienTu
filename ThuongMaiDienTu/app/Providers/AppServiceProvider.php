<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Observers\BaseTranslationObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Core app services are registered automatically by Laravel.
    }

    public function boot(): void
    {
        $this->bootInfrastructure();
        $this->bootObservers();
        $this->bootLoginHistory();
    }

    protected function bootInfrastructure(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);
    }

    protected function bootLoginHistory(): void
    {
        Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                \App\Models\LoginHistory::create([
                    'user_id' => $event->user->user_id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'login_at' => now(),
                ]);
            }
        );
    }

    protected function bootObservers(): void
    {
        Order::observe(OrderObserver::class);

        if (! config('translatable.auto_translate', true)) {
            return;
        }

        foreach ([Product::class, Category::class, Attribute::class, Page::class] as $model) {
            $model::observe(BaseTranslationObserver::class);
        }
    }
}

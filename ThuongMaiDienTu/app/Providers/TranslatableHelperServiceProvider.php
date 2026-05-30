<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TranslatableHelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $helperPath = app_path('Helpers/translatable.php');

        if (is_file($helperPath)) {
            require_once $helperPath;
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('locale')) {
            $request->session()->put('locale', 'vi');
        }

        $locale = $request->session()->get('locale', 'vi');

        $supported = array_keys(config('translatable.supported_locales', ['vi' => 'Tiếng Việt', 'en' => 'English']));

        if (in_array($locale, $supported)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

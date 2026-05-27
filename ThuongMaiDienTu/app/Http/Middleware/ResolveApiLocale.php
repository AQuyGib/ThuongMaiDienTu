<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('translatable.supported_locales', ['vi' => 'Tiếng Việt', 'en' => 'English']));

        $locale = $request->query('locale')
            ?: $request->header('X-Locale')
            ?: $request->header('Accept-Language')
            ?: config('app.locale');

        $locale = $this->normalizeLocale((string) $locale, $supported);

        App::setLocale($locale);
        $request->attributes->set('locale', $locale);

        return $next($request);
    }

    protected function normalizeLocale(string $locale, array $supported): string
    {
        $locale = strtolower(trim(explode(',', $locale)[0] ?? $locale));
        $locale = explode(';', $locale)[0];
        $locale = explode('-', $locale)[0];

        return in_array($locale, $supported, true)
            ? $locale
            : (string) config('translatable.fallback_locale', config('app.fallback_locale', 'vi'));
    }
}

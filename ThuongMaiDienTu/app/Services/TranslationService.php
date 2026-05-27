<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public function translate(string $text, string $sourceLocale = 'vi', string $targetLocale = 'en'): string
    {
        $text = trim($text);

        if ($text === '' || $sourceLocale === $targetLocale) {
            return $text;
        }

        return match (config('translatable.provider', 'google_api')) {
            'package' => $this->translateWithPackage($text, $sourceLocale, $targetLocale),
            default => $this->translateWithGoogleApi($text, $sourceLocale, $targetLocale),
        };
    }

    public function translateMany(array $texts, string $sourceLocale = 'vi', string $targetLocale = 'en'): array
    {
        $result = [];

        foreach ($texts as $key => $value) {
            $result[$key] = is_string($value)
                ? $this->translate($value, $sourceLocale, $targetLocale)
                : $value;
        }

        return $result;
    }

    protected function translateWithGoogleApi(string $text, string $sourceLocale, string $targetLocale): string
    {
        $config = config('translatable.google_api', []);

        if (blank(data_get($config, 'api_key'))) {
            return $text;
        }

        try {
            $response = Http::timeout((int) data_get($config, 'timeout', 20))->get(
                data_get($config, 'endpoint'),
                [
                    'q' => $text,
                    'source' => $sourceLocale,
                    'target' => $targetLocale,
                    'format' => 'text',
                    'key' => data_get($config, 'api_key'),
                ]
            );

            if (! $response->successful()) {
                Log::warning('Google Translate API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $text;
            }

            return (string) data_get($response->json(), 'data.translations.0.translatedText', $text);
        } catch (\Throwable $e) {
            Log::error('TranslationService google_api error', [
                'message' => $e->getMessage(),
            ]);

            return $text;
        }
    }

    protected function translateWithPackage(string $text, string $sourceLocale, string $targetLocale): string
    {
        try {
            $class = config('translatable.package.class');

            if (! class_exists($class)) {
                return $text;
            }

            $translator = app($class);

            if (method_exists($translator, 'setSource')) {
                $translator->setSource($sourceLocale);
            }

            if (method_exists($translator, 'setTarget')) {
                $translator->setTarget($targetLocale);
            }

            if (method_exists($translator, 'translate')) {
                return (string) $translator->translate($text);
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('TranslationService package error', [
                'message' => $e->getMessage(),
            ]);

            return $text;
        }
    }
}

<?php

return [
    'source_locale' => env('TRANSLATABLE_SOURCE_LOCALE', 'vi'),
    'default_target_locale' => env('TRANSLATABLE_TARGET_LOCALE', 'en'),
    'fallback_locale' => env('TRANSLATABLE_FALLBACK_LOCALE', 'vi'),

    'supported_locales' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ],

    'auto_translate' => env('TRANSLATABLE_AUTO_TRANSLATE', true),

    'translate_on' => [
        'created',
        'updated',
    ],

    'default_translatable_columns' => [
        'name',
        'title',
        'slug',
        'description',
        'excerpt',
        'content',
        'short_description',
        'meta_title',
        'meta_description',
        'seo_title',
        'seo_description',
    ],

    'provider' => env('TRANSLATABLE_PROVIDER', 'google_api'),

    'google_api' => [
        'endpoint' => env('GOOGLE_TRANSLATE_ENDPOINT', 'https://translation.googleapis.com/language/translate/v2'),
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'timeout' => env('GOOGLE_TRANSLATE_TIMEOUT', 20),
    ],

    'package' => [
        'class' => env('TRANSLATABLE_PACKAGE_CLASS', \Starcapt\GoogleTranslate\GoogleTranslate::class),
    ],

    'observer' => [
        'sync_on_save' => true,
        'sync_only_dirty' => true,
        'queue_if_available' => env('TRANSLATABLE_QUEUE', false),
    ],

    'translation_suffix' => 'Translation',
    'translation_table_suffix' => '_translations',
];

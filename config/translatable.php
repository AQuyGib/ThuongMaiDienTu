<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Translation Strategy
    |--------------------------------------------------------------------------
    |
    | Cấu hình locale mặc định và locale đích được dùng khi hệ thống tự động
    | dịch nội dung từ ngôn ngữ gốc (thường là tiếng Việt) sang ngôn ngữ khác.
    |
    */
    'source_locale' => env('TRANSLATABLE_SOURCE_LOCALE', 'vi'),
    'default_target_locale' => env('TRANSLATABLE_TARGET_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | Danh sách ngôn ngữ hỗ trợ toàn hệ thống. Key là locale, value là label
    | hiển thị trong admin nếu cần.
    |
    */
    'supported_locales' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Translation
    |--------------------------------------------------------------------------
    |
    | Khi bật, observer/trait sẽ tự động gọi TranslationService sau khi model
    | gốc được lưu. Tắt cấu hình này để manual sync hoặc debug.
    |
    */
    'auto_translate' => env('TRANSLATABLE_AUTO_TRANSLATE', true),
    'translate_on' => [
        'created',
        'updated',
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Translation Columns
    |--------------------------------------------------------------------------
    |
    | Các cột thường gặp trong hệ thống thương mại điện tử. Model nào cần field
    | nào thì chỉ cần khai báo trong $translatable; trait sẽ tự quét theo config.
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Translation Provider
    |--------------------------------------------------------------------------
    |
    | provider: google_api | package
    | - google_api: dùng HTTP Client gọi Google Translate API
    | - package: dùng package dịch thuật (ví dụ starcapt/google-translate-php)
    |
    */
    'provider' => env('TRANSLATABLE_PROVIDER', 'google_api'),

    'google_api' => [
        'endpoint' => env('GOOGLE_TRANSLATE_ENDPOINT', 'https://translation.googleapis.com/language/translate/v2'),
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'timeout' => env('GOOGLE_TRANSLATE_TIMEOUT', 20),
    ],

    'package' => [
        'class' => env('TRANSLATABLE_PACKAGE_CLASS', \Starcapt\GoogleTranslate\GoogleTranslate::class),
    ],

    /*
    |--------------------------------------------------------------------------
    | Observer Behavior
    |--------------------------------------------------------------------------
    |
    | sync_on_save: đồng bộ ngay sau khi model save
    | sync_only_dirty: chỉ dịch lại khi field translatable thực sự thay đổi
    | queue_if_available: ưu tiên đẩy sang queue nếu hệ thống có queue worker
    |
    */
    'observer' => [
        'sync_on_save' => true,
        'sync_only_dirty' => true,
        'queue_if_available' => env('TRANSLATABLE_QUEUE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Convention
    |--------------------------------------------------------------------------
    |
    | Quy ước naming cho model translation. Ví dụ Product => ProductTranslation
    | và bảng product_translations. Nếu dự án có naming custom, có thể override
    | tại model thông qua các method trong BaseTranslationTrait.
    |
    */
    'translation_suffix' => 'Translation',
    'translation_table_suffix' => '_translations',
];

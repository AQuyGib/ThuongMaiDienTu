<?php

namespace App\Services;

class AuditMasker
{
    private static array $blacklist = [
        'password', 'password_hash', 'api_token', 'client_secret',
        'access_token', 'otp_code', 'credit_card', 'cvv', 'password_confirmation'
    ];

    /**
     * Che giấu đệ quy các trường nhạy cảm trong mảng dữ liệu
     *
     * @param array|null $data
     * @return array|null
     */
    public static function mask(?array $data): ?array
    {
        if (is_null($data)) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::$blacklist)) {
                $data[$key] = '******** [MASKED]';
            } elseif (is_array($value)) {
                $data[$key] = self::mask($value);
            }
        }

        return $data;
    }
}

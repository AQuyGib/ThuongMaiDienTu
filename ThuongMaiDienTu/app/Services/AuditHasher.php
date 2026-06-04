<?php

namespace App\Services;

class AuditHasher
{
    /**
     * Tạo mã Hash liên chuỗi cho dòng log hiện tại
     *
     * @param array $payload Dữ liệu dòng log hiện tại
     * @param string|null $previousHash Mã hash của dòng log ngay trước đó
     * @return string Mã SHA-256 Hash Chain (64 ký tự hex)
     */
    public static function generateHashChain(array $payload, ?string $previousHash): string
    {
        // 1. Chuẩn hóa chuỗi JSON (Canonicalization)
        $oldJson = self::canonicalizeJson($payload['old_values'] ?? null);
        $newJson = self::canonicalizeJson($payload['new_values'] ?? null);

        // 2. Gom chuỗi (Concatenation) theo quy tắc cố định
        $rawString = sprintf(
            "%s|%s|%d|%s|%s|%s|%s|%s|%s|%s",
            $payload['event'],
            $payload['causer_type'],
            (int) $payload['causer_id'],
            $payload['subject_type'] ?? '',
            $payload['subject_id'] ?? '',
            $oldJson,
            $newJson,
            $payload['ip_address'] ?? '',
            $payload['created_at'],
            $previousHash ?? str_repeat('0', 64)
        );

        // 3. Sử dụng mã băm HMAC-SHA256 với Salt khóa mật mã hệ thống
        $saltKey = config('app.key', 'SomeDefaultBackupSigningKeySecret123!');
        return hash_hmac('sha256', $rawString, $saltKey);
    }

    /**
     * Sắp xếp khóa JSON theo thứ tự chữ cái để đảm bảo tính nhất quán (Deterministic JSON)
     */
    public static function canonicalizeJson($value): string
    {
        if (empty($value)) {
            return '{}';
        }
        
        $array = is_array($value) ? $value : json_decode($value, true);
        if (!$array || !is_array($array)) {
            return '{}';
        }
        
        ksort($array);
        return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

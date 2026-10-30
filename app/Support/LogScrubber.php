<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Menyaring rahasia dari log dan properti audit (rules/12): token, secret, password, NIK 16 digit.
 */
final class LogScrubber
{
    public const MASK = '[disembunyikan]';

    /** Kunci yang nilainya selalu disembunyikan (cocok sebagian, tanpa peduli huruf besar). */
    private const SENSITIVE_KEYS = ['password', 'token', 'secret', 'nik', 'mother', 'ibu', 'authorization', 'api_key'];

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    public static function scrubArray(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                $out[$key] = self::MASK;

                continue;
            }
            $out[$key] = is_array($value) ? self::scrubArray($value) : (is_string($value) ? self::scrubString($value) : $value);
        }

        return $out;
    }

    public static function scrubString(string $text): string
    {
        // Authorization: Bearer xxx / token Sanctum "id|plain"
        $text = preg_replace('/Bearer\s+[A-Za-z0-9|._\-]+/i', 'Bearer '.self::MASK, $text) ?? $text;
        $text = preg_replace('/\b\d+\|[A-Za-z0-9]{20,}\b/', self::MASK, $text) ?? $text;

        // NIK: 16 digit berurutan (mis. 3201010101010001)
        return preg_replace('/(?<!\d)\d{16}(?!\d)/', self::MASK, $text) ?? $text;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $k = strtolower($key);
        foreach (self::SENSITIVE_KEYS as $needle) {
            if (str_contains($k, $needle)) {
                return true;
            }
        }

        return false;
    }
}

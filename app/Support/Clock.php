<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Satu-satunya pintu waktu. Test membekukan lewat Carbon::setTestNow().
 * DB menyimpan UTC; "hari akademik" dihitung di zona kampus (config campus.timezone).
 */
final class Clock
{
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    public static function campusNow(): CarbonImmutable
    {
        return self::now()->setTimezone((string) config('campus.timezone'));
    }

    /** Tanggal hari ini di zona kampus (tanpa jam). */
    public static function campusToday(): CarbonImmutable
    {
        return self::campusNow()->startOfDay();
    }
}

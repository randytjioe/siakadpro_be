<?php

declare(strict_types=1);

namespace App\Domain\Academic\Enums;

/** Jenis tanggal per prodi dalam satu periode (rules/06 §1). */
enum PeriodDateType: string
{
    case KrsFilling = 'krs';
    case Advising = 'advising';
    case KrsChange = 'krs_change';
    case Lectures = 'lectures';
    case Midterm = 'midterm';
    case Final = 'final';
    case Reporting = 'reporting';
    case Closing = 'closing';

    public function label(): string
    {
        return match ($this) {
            self::KrsFilling => 'Pengisian KRS',
            self::Advising => 'Persetujuan dosen wali',
            self::KrsChange => 'Perubahan KRS',
            self::Lectures => 'Perkuliahan',
            self::Midterm => 'UTS',
            self::Final => 'UAS',
            self::Reporting => 'Pelaporan PDDikti',
            self::Closing => 'Tutup periode',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::KrsFilling, self::KrsChange => 'brand',
            self::Advising => 'info',
            self::Lectures => 'ok',
            self::Midterm, self::Final => 'amber',
            self::Reporting => 'hermes',
            self::Closing => 'muted',
        };
    }

    public function icon(): string
    {
        return 'calendar';
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}

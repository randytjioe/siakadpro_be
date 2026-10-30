<?php

declare(strict_types=1);

namespace App\Domain\People\Enums;

enum StudentStatus: string
{
    case Aktif = 'aktif';
    case Cuti = 'cuti';
    case Lulus = 'lulus';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Cuti => 'Cuti',
            self::Lulus => 'Lulus',
            self::Nonaktif => 'Nonaktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aktif => 'ok',
            self::Cuti => 'amber',
            self::Lulus => 'info',
            self::Nonaktif => 'muted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Aktif => 'check',
            self::Cuti => 'pause',
            self::Lulus => 'graduation-cap',
            self::Nonaktif => 'ban',
        };
    }
}

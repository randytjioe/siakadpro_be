<?php

declare(strict_types=1);

namespace App\Domain\Academic\Enums;

enum PeriodStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Active => 'Berjalan',
            self::Closed => 'Ditutup',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'muted',
            self::Active => 'info',
            self::Closed => 'ok',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'pencil',
            self::Active => 'clock',
            self::Closed => 'lock',
        };
    }
}

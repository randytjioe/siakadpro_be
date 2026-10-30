<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Enums;

enum CourseKind: string
{
    case Wajib = 'wajib';
    case Pilihan = 'pilihan';

    public function label(): string
    {
        return match ($this) {
            self::Wajib => 'Wajib',
            self::Pilihan => 'Pilihan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Wajib => 'brand',
            self::Pilihan => 'amber',
        };
    }

    public function icon(): string
    {
        return 'book';
    }
}

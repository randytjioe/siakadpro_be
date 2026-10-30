<?php

declare(strict_types=1);

namespace App\Domain\People\Enums;

enum ImportType: string
{
    case Students = 'students';
    case Lecturers = 'lecturers';

    public function label(): string
    {
        return match ($this) {
            self::Students => 'Data mahasiswa',
            self::Lecturers => 'Data dosen',
        };
    }

    public function color(): string
    {
        return 'brand';
    }

    public function icon(): string
    {
        return 'upload';
    }
}

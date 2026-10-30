<?php

declare(strict_types=1);

namespace App\Domain\People\Enums;

enum ImportRowStatus: string
{
    case Baru = 'baru';
    case Ubah = 'ubah';
    case Duplikat = 'duplikat';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Ubah => 'Ubah',
            self::Duplikat => 'Duplikat',
            self::Invalid => 'Tidak valid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Baru => 'ok',
            self::Ubah => 'info',
            self::Duplikat => 'amber',
            self::Invalid => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Baru => 'plus',
            self::Ubah => 'pencil',
            self::Duplikat => 'copy',
            self::Invalid => 'alert',
        };
    }

    public function willWrite(): bool
    {
        return $this === self::Baru || $this === self::Ubah;
    }
}

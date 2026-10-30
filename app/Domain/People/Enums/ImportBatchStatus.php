<?php

declare(strict_types=1);

namespace App\Domain\People\Enums;

enum ImportBatchStatus: string
{
    case Siap = 'siap';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case Gagal = 'gagal';

    public function label(): string
    {
        return match ($this) {
            self::Siap => 'Siap diterapkan',
            self::Berjalan => 'Berjalan',
            self::Selesai => 'Selesai',
            self::Gagal => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Siap => 'amber',
            self::Berjalan => 'info',
            self::Selesai => 'ok',
            self::Gagal => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Siap => 'clock',
            self::Berjalan => 'refresh',
            self::Selesai => 'check',
            self::Gagal => 'alert',
        };
    }
}

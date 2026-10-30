<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

/**
 * Tujuh peran (rules/05). Dosen wali BUKAN peran: itu penugasan (advisor_assignments).
 * label/warna/ikon satu sumber untuk web dan app; warna = nama token desain (sp.css).
 */
enum Role: string
{
    case Mahasiswa = 'mahasiswa';
    case Dosen = 'dosen';
    case Baak = 'baak';
    case Keuangan = 'keuangan';
    case Kaprodi = 'kaprodi';
    case Pimpinan = 'pimpinan';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::Dosen => 'Dosen',
            self::Baak => 'Admin Akademik · BAAK',
            self::Keuangan => 'Keuangan',
            self::Kaprodi => 'Kaprodi',
            self::Pimpinan => 'Pimpinan',
            self::SuperAdmin => 'Super Admin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Mahasiswa, self::Dosen => 'brand',
            self::Baak => 'info',
            self::Keuangan => 'ok',
            self::Kaprodi => 'amber',
            self::Pimpinan => 'hermes',
            self::SuperAdmin => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Mahasiswa => 'graduation-cap',
            self::Dosen => 'presentation',
            self::Baak => 'building',
            self::Keuangan => 'wallet',
            self::Kaprodi => 'compass',
            self::Pimpinan => 'chart',
            self::SuperAdmin => 'shield',
        };
    }

    /** Urutan bawaan peran aktif saat login pada akun multi peran. */
    public function priority(): int
    {
        return match ($this) {
            self::SuperAdmin => 1,
            self::Pimpinan => 2,
            self::Baak => 3,
            self::Keuangan => 4,
            self::Kaprodi => 5,
            self::Dosen => 6,
            self::Mahasiswa => 7,
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}

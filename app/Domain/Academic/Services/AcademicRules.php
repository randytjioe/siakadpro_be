<?php

declare(strict_types=1);

namespace App\Domain\Academic\Services;

use App\Domain\Academic\Models\Setting;

/**
 * SATU-SATUNYA pintu aturan akademik yang dapat diatur (rules/03). Nilai = baris `settings` bila ada,
 * selain itu bawaan `config/academic.php`. DILARANG menulis angka aturan di tempat lain.
 */
final class AcademicRules
{
    /** @var array<string, mixed>|null */
    private ?array $overrides = null;

    public function get(string $key): mixed
    {
        $this->overrides ??= Setting::query()->pluck('value', 'key')->all();

        return array_key_exists($key, $this->overrides) ? $this->overrides[$key] : config('academic.'.$key);
    }

    /** @return array<string, mixed> seluruh aturan (bawaan + override) */
    public function all(): array
    {
        $out = [];
        foreach (array_keys(config('academic')) as $key) {
            $out[$key] = $this->get($key);
        }

        return $out;
    }

    /** @return list<string> kunci yang boleh diatur */
    public static function keys(): array
    {
        return array_keys(config('academic'));
    }

    public function forget(): void
    {
        $this->overrides = null;
    }

    /** @return list<array{letter: string, min: float|int, points: float|int}> */
    public function gradeScale(): array
    {
        return $this->get('grade_scale');
    }

    /** @return list<string> */
    public function gradeLetters(): array
    {
        return array_map(fn (array $row) => (string) $row['letter'], $this->gradeScale());
    }

    public function isGradeLetter(string $letter): bool
    {
        return in_array($letter, $this->gradeLetters(), true);
    }

    public function pattern(string $name): string
    {
        return (string) $this->get($name.'_pattern');
    }

    public function int(string $key): int
    {
        return (int) $this->get($key);
    }

    /** Aturan validasi nilai pengaturan (dipakai SettingsService). @return array<string, mixed> */
    public static function validationRules(): array
    {
        return [
            'grade_scale' => ['array', 'min:2'],
            'grade_scale.*.letter' => ['required', 'string', 'max:3', 'distinct'],
            'grade_scale.*.min' => ['required', 'numeric', 'min:0', 'max:100'],
            'grade_scale.*.points' => ['required', 'numeric', 'min:0', 'max:4'],
            'nim_pattern' => ['string', 'max:100'],
            'nidn_pattern' => ['string', 'max:100'],
            'nip_pattern' => ['string', 'max:100'],
            'phone_pattern' => ['string', 'max:100'],
            'import_max_kb' => ['integer', 'min:1', 'max:20480'],
            'import_max_rows' => ['integer', 'min:1', 'max:50000'],
            'import_chunk' => ['integer', 'min:1', 'max:1000'],
            'import_retention_hours' => ['integer', 'min:1', 'max:720'],
            'risk_gpa_below' => ['numeric', 'min:0', 'max:4'],
            'qr_window_seconds' => ['integer', 'min:10', 'max:300'],
            'qr_window_tolerance' => ['integer', 'min:0', 'max:5'],
            'va_expiry_hours' => ['integer', 'min:1', 'max:720'],
        ];
    }
}

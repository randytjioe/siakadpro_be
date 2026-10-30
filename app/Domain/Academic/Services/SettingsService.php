<?php

declare(strict_types=1);

namespace App\Domain\Academic\Services;

use App\Domain\Academic\Models\Setting;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\ActivityLogger;
use App\Support\RuleViolation;
use Illuminate\Support\Facades\DB;

final class SettingsService
{
    public function __construct(
        private readonly AcademicRules $rules,
        private readonly ActivityLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $values  hanya kunci yang dikenal (divalidasi FormRequest)
     * @return array<string, mixed> seluruh aturan setelah perubahan
     */
    public function update(array $values, User $actor): array
    {
        $this->assertGradeScale($values);
        $this->assertPatterns($values);

        $before = [];
        $after = [];
        DB::transaction(function () use ($values, &$before, &$after) {
            foreach ($values as $key => $value) {
                $before[$key] = $this->rules->get($key);
                Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
                $after[$key] = $value;
            }
        });
        $this->rules->forget();

        $this->audit->record('settings.updated', $actor, null, ['before' => $before, 'after' => $after]);

        return $this->rules->all();
    }

    /** @param  array<string, mixed>  $values */
    private function assertGradeScale(array $values): void
    {
        if (! array_key_exists('grade_scale', $values)) {
            return;
        }
        $rows = array_values($values['grade_scale']);
        $mins = array_map(fn ($r) => (float) $r['min'], $rows);
        $points = array_map(fn ($r) => (float) $r['points'], $rows);

        $problems = [];
        if (min($mins) !== 0.0) {
            $problems[] = 'Skala harus memiliki batas bawah 0.';
        }
        for ($i = 1; $i < count($rows); $i++) {
            if ($mins[$i] >= $mins[$i - 1] || $points[$i] > $points[$i - 1]) {
                $problems[] = 'Skala harus menurun: batas bawah dan bobot mutu tidak boleh naik pada baris berikutnya.';
                break;
            }
        }
        if ($problems !== []) {
            throw new RuleViolation('Data tidak valid.', ['grade_scale' => $problems]);
        }
    }

    /** @param  array<string, mixed>  $values */
    private function assertPatterns(array $values): void
    {
        foreach (['nim_pattern', 'nidn_pattern', 'nip_pattern', 'phone_pattern'] as $key) {
            if (isset($values[$key]) && @preg_match($values[$key], '') === false) {
                throw new RuleViolation('Data tidak valid.', [$key => ['Pola regex tidak valid.']]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function current(): array
    {
        return $this->rules->all();
    }

    public function has(string $key): bool
    {
        return Setting::query()->where('key', $key)->exists();
    }
}

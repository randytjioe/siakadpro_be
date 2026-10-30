<?php

declare(strict_types=1);

namespace App\Domain\Academic\Services;

use App\Domain\Academic\Enums\PeriodDateType;
use App\Domain\Academic\Enums\PeriodStatus;
use App\Domain\Academic\Models\AcademicPeriod;
use App\Domain\Academic\Models\PeriodDate;
use App\Domain\Academic\Models\StudyProgram;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\ActivityLogger;
use App\Support\Clock;
use App\Support\Conflict;
use App\Support\RuleViolation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Periode akademik dan tanggal per prodi (rules/06 §1). `phases()` adalah satu-satunya jalan mengetahui fase. */
final class PeriodService
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /** @param  array{year: int, term: int}  $data */
    public function create(array $data, User $actor): AcademicPeriod
    {
        $code = $data['year'].$data['term'];
        if (AcademicPeriod::query()->where('code', $code)->exists()) {
            throw new RuleViolation('Data tidak valid.', ['year' => ["Periode {$code} sudah ada."]]);
        }

        $term = $data['term'] === 1 ? 'Ganjil' : 'Genap';
        $period = AcademicPeriod::create([
            'code' => $code,
            'name' => sprintf('%s %d/%d', $term, $data['year'], $data['year'] + 1),
            'year' => $data['year'],
            'term' => $data['term'],
            'status' => PeriodStatus::Draft,
        ]);
        $this->audit->record('periods.created', $actor, $period, ['code' => $code]);

        return $period;
    }

    /** Periode ditutup hanya boleh diubah super_admin dengan alasan (rules/06 §1). */
    public function update(AcademicPeriod $period, array $data, User $actor, ?string $reason = null): AcademicPeriod
    {
        $this->assertEditable($period, $actor, $reason);
        $before = ['name' => $period->name];
        $period->update(['name' => $data['name']]);
        $this->audit->record('periods.updated', $actor, $period, [
            'before' => $before, 'after' => ['name' => $period->name], 'reason' => $reason,
        ]);

        return $period;
    }

    public function activate(AcademicPeriod $period, User $actor): AcademicPeriod
    {
        if ($period->status !== PeriodStatus::Draft) {
            throw new Conflict('Hanya periode berstatus draf yang dapat diaktifkan.');
        }

        return DB::transaction(function () use ($period, $actor) {
            // Satu periode aktif pada satu waktu. Dikunci agar dua aktivasi bersamaan tidak lolos.
            $other = AcademicPeriod::query()->where('status', PeriodStatus::Active->value)->lockForUpdate()->first();
            if ($other !== null) {
                throw new Conflict("Periode {$other->code} masih berjalan. Tutup dulu sebelum mengaktifkan periode lain.");
            }
            $period->update(['status' => PeriodStatus::Active]);
            $this->audit->record('periods.activated', $actor, $period, [
                'before' => ['status' => PeriodStatus::Draft->value], 'after' => ['status' => PeriodStatus::Active->value],
            ]);

            return $period;
        });
    }

    public function close(AcademicPeriod $period, User $actor): AcademicPeriod
    {
        if ($period->status !== PeriodStatus::Active) {
            throw new Conflict('Hanya periode berjalan yang dapat ditutup.');
        }
        $period->update(['status' => PeriodStatus::Closed, 'closed_at' => Clock::now()]);
        $this->audit->record('periods.closed', $actor, $period, [
            'before' => ['status' => PeriodStatus::Active->value], 'after' => ['status' => PeriodStatus::Closed->value],
        ]);

        return $period;
    }

    /**
     * Ganti seluruh tanggal satu prodi pada satu periode.
     *
     * @param  list<array{type: string, starts_on: string, ends_on: string}>  $dates
     */
    public function setDates(AcademicPeriod $period, StudyProgram $program, array $dates, User $actor, ?string $reason = null): Collection
    {
        $this->assertEditable($period, $actor, $reason);

        $seen = [];
        foreach ($dates as $i => $row) {
            if (isset($seen[$row['type']])) {
                throw new RuleViolation('Data tidak valid.', ["dates.$i.type" => ['Jenis tanggal duplikat.']]);
            }
            $seen[$row['type']] = true;
            if ($row['starts_on'] > $row['ends_on']) {
                throw new RuleViolation('Data tidak valid.', ["dates.$i.ends_on" => ['Tanggal selesai tidak boleh sebelum tanggal mulai.']]);
            }
        }

        $before = $this->datesOf($period, $program);

        DB::transaction(function () use ($period, $program, $dates) {
            PeriodDate::query()
                ->where('academic_period_id', $period->id)->where('study_program_id', $program->id)
                ->whereNotIn('type', array_column($dates, 'type'))->delete();
            foreach ($dates as $row) {
                PeriodDate::query()->updateOrCreate(
                    ['academic_period_id' => $period->id, 'study_program_id' => $program->id, 'type' => $row['type']],
                    ['starts_on' => $row['starts_on'], 'ends_on' => $row['ends_on']],
                );
            }
        });

        $after = $this->datesOf($period, $program);
        $this->audit->record('periods.dates_changed', $actor, $period, [
            'study_program' => $program->code, 'before' => $before, 'after' => $after, 'reason' => $reason,
        ]);

        return PeriodDate::query()
            ->where('academic_period_id', $period->id)->where('study_program_id', $program->id)
            ->orderBy('starts_on')->get();
    }

    /**
     * Fase yang sedang berjalan untuk satu prodi pada tanggal kampus tertentu (default: hari ini).
     * Hanya periode `active` yang menghasilkan fase.
     *
     * @return list<PeriodDateType>
     */
    public function phases(StudyProgram $program, ?CarbonInterface $at = null): array
    {
        $period = AcademicPeriod::query()->where('status', PeriodStatus::Active->value)->first();
        if ($period === null) {
            return [];
        }
        $day = ($at ?? Clock::campusToday())->toDateString();

        return PeriodDate::query()
            ->where('academic_period_id', $period->id)
            ->where('study_program_id', $program->id)
            ->whereDate('starts_on', '<=', $day)->whereDate('ends_on', '>=', $day)
            ->orderBy('starts_on')->get()
            ->map(fn (PeriodDate $d) => $d->type)->all();
    }

    public function isOpen(StudyProgram $program, PeriodDateType $type, ?CarbonInterface $at = null): bool
    {
        return in_array($type, $this->phases($program, $at), true);
    }

    public function current(): ?AcademicPeriod
    {
        return AcademicPeriod::query()->where('status', PeriodStatus::Active->value)->first();
    }

    private function assertEditable(AcademicPeriod $period, User $actor, ?string $reason): void
    {
        if ($period->status !== PeriodStatus::Closed) {
            return;
        }
        if (! $actor->isActiveRole(Role::SuperAdmin)) {
            throw new Conflict('Periode sudah ditutup. Hanya Super Admin yang dapat mengubahnya.');
        }
        if ($reason === null || mb_strlen(trim($reason)) < 5) {
            throw new RuleViolation('Data tidak valid.', ['reason' => ['Alasan wajib diisi (minimal 5 karakter) untuk mengubah periode yang sudah ditutup.']]);
        }
    }

    /** @return array<string, array{starts_on: string, ends_on: string}> */
    private function datesOf(AcademicPeriod $period, StudyProgram $program): array
    {
        return PeriodDate::query()
            ->where('academic_period_id', $period->id)->where('study_program_id', $program->id)
            ->get()->mapWithKeys(fn (PeriodDate $d) => [$d->type->value => [
                'starts_on' => $d->starts_on->toDateString(), 'ends_on' => $d->ends_on->toDateString(),
            ]])->all();
    }
}

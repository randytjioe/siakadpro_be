<?php

declare(strict_types=1);

namespace App\Domain\Academic\Models;

use App\Domain\Academic\Enums\PeriodDateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodDate extends Model
{
    protected $fillable = ['academic_period_id', 'study_program_id', 'type', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return [
            'type' => PeriodDateType::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }
}

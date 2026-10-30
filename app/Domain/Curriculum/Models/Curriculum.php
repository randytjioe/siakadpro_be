<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use App\Domain\Academic\Models\StudyProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    protected $table = 'curricula';

    protected $fillable = ['study_program_id', 'code', 'name', 'effective_from_year', 'is_active'];

    protected function casts(): array
    {
        return ['effective_from_year' => 'integer', 'is_active' => 'boolean'];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Curriculum\Models;

use App\Domain\Academic\Models\StudyProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cpl extends Model
{
    protected $fillable = ['study_program_id', 'code', 'description'];

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function cpmks(): BelongsToMany
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_cpl')->withPivot('weight')->withTimestamps();
    }
}

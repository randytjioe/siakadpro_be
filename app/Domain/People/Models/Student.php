<?php

declare(strict_types=1);

namespace App\Domain\People\Models;

use App\Domain\Academic\Models\StudyProgram;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Domain\People\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'study_program_id', 'nim', 'name', 'cohort', 'status',
        'nik', 'mother_name', 'birth_date', 'phone',
    ];

    protected $hidden = ['nik', 'mother_name'];

    protected function casts(): array
    {
        return [
            'cohort' => 'integer',
            'status' => StudentStatus::class,
            'nik' => 'encrypted',
            'mother_name' => 'encrypted',
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function advisorAssignments(): HasMany
    {
        return $this->hasMany(AdvisorAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AdvisorAssignment::class)->whereNull('ends_on');
    }

    /**
     * Lingkup data per peran aktif (rules/05 "Query dibatasi lingkup"): bukan sekadar disaring di Resource.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->activeRole()) {
            Role::Baak, Role::SuperAdmin, Role::Keuangan, Role::Pimpinan => $query,
            Role::Kaprodi => $query->whereIn('students.study_program_id', $user->scopedProgramIds()),
            Role::Dosen => $query->whereHas('currentAssignment', fn (Builder $q) => $q->whereHas(
                'lecturer', fn (Builder $l) => $l->where('lecturers.user_id', $user->id),
            )),
            Role::Mahasiswa => $query->where('students.user_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }
}

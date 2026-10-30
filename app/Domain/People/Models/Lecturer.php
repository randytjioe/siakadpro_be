<?php

declare(strict_types=1);

namespace App\Domain\People\Models;

use App\Domain\Academic\Models\StudyProgram;
use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lecturer extends Model
{
    protected $fillable = ['user_id', 'study_program_id', 'nidn', 'nip', 'name', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    /** Identitas kerja: NIDN bila ada, selain itu NIP. */
    public function identifier(): ?string
    {
        return $this->nidn ?? $this->nip;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->activeRole()) {
            Role::Baak, Role::SuperAdmin, Role::Pimpinan => $query,
            Role::Kaprodi => $query->whereIn('lecturers.study_program_id', $user->scopedProgramIds()),
            Role::Dosen => $query->where('lecturers.user_id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };
    }
}

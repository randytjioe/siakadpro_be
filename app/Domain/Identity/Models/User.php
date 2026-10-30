<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Academic\Models\StudyProgram;
use App\Domain\Identity\Enums\Role;
use App\Domain\People\Models\Lecturer;
use App\Domain\People\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['username', 'name', 'email', 'password', 'is_active', 'must_change_password'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function studyPrograms(): BelongsToMany
    {
        return $this->belongsToMany(StudyProgram::class, 'study_program_scopes')->withTimestamps();
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function lecturer(): HasOne
    {
        return $this->hasOne(Lecturer::class);
    }

    /** @return list<Role> peran yang dimiliki, urut prioritas */
    public function roles(): array
    {
        $this->loadMissing('roleAssignments');
        $roles = $this->roleAssignments->map(fn (RoleAssignment $a) => $a->role)->all();
        usort($roles, fn (Role $a, Role $b) => $a->priority() <=> $b->priority());

        return array_values($roles);
    }

    public function hasRole(Role $role): bool
    {
        return in_array($role, $this->roles(), true);
    }

    /**
     * Peran aktif = yang dibawa token saat ini. Gagal-tertutup: tanpa token Sanctum nyata,
     * atau peran sudah dicabut dari akun, hasilnya null sehingga semua pemeriksaan peran ditolak.
     */
    public function activeRole(): ?Role
    {
        $token = $this->currentAccessToken();
        if (! $token instanceof PersonalAccessToken || ! $token->active_role) {
            return null;
        }
        $role = Role::tryFrom((string) $token->active_role);

        return $role !== null && $this->hasRole($role) ? $role : null;
    }

    public function isActiveRole(Role ...$roles): bool
    {
        $active = $this->activeRole();

        return $active !== null && in_array($active, $roles, true);
    }

    /** @return list<int> prodi dalam lingkup kaprodi */
    public function scopedProgramIds(): array
    {
        return $this->studyPrograms()->pluck('study_programs.id')->map(fn ($id) => (int) $id)->all();
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\RoleAssignment;
use App\Domain\Identity\Models\User;
use App\Support\Conflict;
use App\Support\RuleViolation;
use Illuminate\Support\Facades\DB;

/** Pengelolaan akun staf, peran, dan lingkup kaprodi (hanya super_admin; rules/05). */
final class UserAdminService
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /**
     * @param  list<string>  $roles
     * @return array{user: User, temporary_password: string}
     */
    public function create(array $data, array $roles, User $actor): array
    {
        $temporary = PasswordService::generate();

        $user = DB::transaction(function () use ($data, $roles, $actor, $temporary) {
            $user = User::create([
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'password' => $temporary,
                'is_active' => true,
                'must_change_password' => true,
            ]);
            $this->writeRoles($user, $roles);
            $this->audit->record('users.created', $actor, $user, ['roles' => $roles]);

            return $user;
        });

        return ['user' => $user->refresh(), 'temporary_password' => $temporary];
    }

    /** @param  list<string>  $roles */
    public function setRoles(User $target, array $roles, User $actor): User
    {
        $new = array_values(array_unique($roles));
        if ($new === []) {
            throw new RuleViolation('Data tidak valid.', ['roles' => ['Akun harus memiliki minimal satu peran.']]);
        }

        $before = array_map(fn (Role $r) => $r->value, $target->roles());

        if (in_array(Role::SuperAdmin->value, $before, true) && ! in_array(Role::SuperAdmin->value, $new, true)
            && $this->otherActiveSuperAdmins($target) === 0) {
            throw new Conflict('Tidak dapat mencabut peran Super Admin terakhir.');
        }

        DB::transaction(function () use ($target, $new, $before, $actor) {
            $this->writeRoles($target, $new);
            // Token yang membawa peran yang baru dicabut tidak boleh hidup lagi.
            $removed = array_values(array_diff($before, $new));
            if ($removed !== []) {
                $target->tokens()->whereIn('active_role', $removed)->delete();
            }
            $this->audit->record('users.roles_changed', $actor, $target, [
                'before' => ['roles' => $before],
                'after' => ['roles' => $new],
            ]);
        });

        return $target->refresh();
    }

    /** @param  list<int>  $programIds */
    public function setProgramScopes(User $target, array $programIds, User $actor): User
    {
        if (! $target->hasRole(Role::Kaprodi)) {
            throw new RuleViolation('Data tidak valid.', ['study_program_ids' => ['Lingkup prodi hanya untuk akun berperan Kaprodi.']]);
        }
        $before = $target->scopedProgramIds();
        $target->studyPrograms()->sync($programIds);
        $this->audit->record('users.scopes_changed', $actor, $target, [
            'before' => ['study_program_ids' => $before],
            'after' => ['study_program_ids' => array_values($programIds)],
        ]);

        return $target;
    }

    public function setActive(User $target, bool $active, User $actor): User
    {
        if (! $active) {
            if ($target->is($actor)) {
                throw new Conflict('Anda tidak dapat menonaktifkan akun sendiri.');
            }
            if ($target->hasRole(Role::SuperAdmin) && $this->otherActiveSuperAdmins($target) === 0) {
                throw new Conflict('Tidak dapat menonaktifkan Super Admin terakhir.');
            }
        }

        $target->forceFill(['is_active' => $active])->save();
        if (! $active) {
            $target->tokens()->delete();
        }
        $this->audit->record($active ? 'users.activated' : 'users.deactivated', $actor, $target);

        return $target;
    }

    private function otherActiveSuperAdmins(User $excluding): int
    {
        return RoleAssignment::query()
            ->where('role', Role::SuperAdmin->value)
            ->where('user_id', '!=', $excluding->id)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->count();
    }

    /** @param  list<string>  $roles */
    private function writeRoles(User $user, array $roles): void
    {
        RoleAssignment::query()->where('user_id', $user->id)->whereNotIn('role', $roles)->delete();
        foreach ($roles as $role) {
            RoleAssignment::query()->firstOrCreate(['user_id' => $user->id, 'role' => $role]);
        }
        $user->unsetRelation('roleAssignments');
    }
}

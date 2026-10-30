<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Support\Forbidden;
use Laravel\Sanctum\PersonalAccessToken;

/** Berganti peran aktif tanpa login ulang. Peran yang tidak dimiliki ditolak (403) dan dicatat. */
final class RoleSwitcher
{
    public function __construct(private readonly ActivityLogger $audit) {}

    public function switch(User $user, string $requested): Role
    {
        $role = Role::tryFrom($requested);
        $token = $user->currentAccessToken();

        if ($role === null || ! $user->hasRole($role) || ! $token instanceof PersonalAccessToken) {
            $this->audit->record('auth.role_switch_denied', $user, $user, ['requested' => $requested]);
            throw new Forbidden('Anda tidak memiliki peran tersebut.');
        }

        $before = $token->active_role;
        $token->forceFill(['active_role' => $role->value])->save();
        $this->audit->record('auth.role_switched', $user, $user, [
            'before' => ['active_role' => $before],
            'after' => ['active_role' => $role->value],
        ]);

        return $role;
    }
}

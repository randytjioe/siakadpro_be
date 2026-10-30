<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Models\User;
use App\Support\AuthFailure;
use App\Support\Clock;
use App\Support\Forbidden;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/** Login: username/NIM/NIP + password -> token Sanctum yang membawa peran aktif (rules/05). */
final class Authenticator
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /**
     * @return array{user: User, token: string, role: Role}
     */
    public function attempt(string $username, string $password): array
    {
        $user = User::query()->where('username', $username)->first();

        // Hash::check tetap dijalankan bila akun tidak ada agar waktu respons tidak membocorkan keberadaan akun.
        $valid = Hash::check($password, $user?->password ?? Hash::make(bin2hex(random_bytes(8))));

        if ($user === null || ! $valid) {
            $this->audit->record('auth.login_failed', null, null, ['username' => $username, 'reason' => 'kredensial']);
            throw new AuthFailure('Username atau kata sandi salah.');
        }
        if (! $user->is_active) {
            $this->audit->record('auth.login_failed', $user, $user, ['reason' => 'akun_nonaktif']);
            throw new AuthFailure('Username atau kata sandi salah.');
        }

        $roles = $user->roles();
        if ($roles === []) {
            $this->audit->record('auth.login_failed', $user, $user, ['reason' => 'tanpa_peran']);
            throw new Forbidden('Akun ini belum memiliki peran. Hubungi admin.');
        }

        $role = $roles[0];
        $token = $this->issueToken($user, $role);
        $user->forceFill(['last_login_at' => Clock::now()])->save();

        $user->withAccessToken($token->accessToken);
        $this->audit->record('auth.login', $user, $user, ['active_role' => $role->value]);

        return ['user' => $user, 'token' => $token->plainTextToken, 'role' => $role];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        $this->audit->record('auth.logout', $user, $user);
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    private function issueToken(User $user, Role $role): \Laravel\Sanctum\NewAccessToken
    {
        $new = $user->createToken('api');
        $new->accessToken->forceFill(['active_role' => $role->value])->save();

        return $new;
    }
}

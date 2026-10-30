<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Models\User;
use App\Support\RuleViolation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

final class PasswordService
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /** Ganti password sendiri; token lain milik akun dicabut, token saat ini dipertahankan. */
    public function change(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, $user->password)) {
            throw new RuleViolation('Data tidak valid.', ['current_password' => ['Kata sandi saat ini salah.']]);
        }
        $user->forceFill(['password' => $new, 'must_change_password' => false])->save();

        $keep = $user->currentAccessToken();
        $user->tokens()
            ->when($keep instanceof PersonalAccessToken, fn ($q) => $q->where('id', '!=', $keep->id))
            ->delete();

        $this->audit->record('auth.password_changed', $user, $user);
    }

    /**
     * Reset oleh super_admin. Mengembalikan kata sandi sementara SEKALI (tidak disimpan dalam bentuk asli);
     * akun wajib menggantinya saat masuk. Semua token target dicabut.
     */
    public function reset(User $target, User $actor): string
    {
        $temporary = self::generate();
        $target->forceFill(['password' => $temporary, 'must_change_password' => true])->save();
        $target->tokens()->delete();

        $this->audit->record('users.password_reset', $actor, $target, ['target' => $target->username]);

        return $temporary;
    }

    public static function generate(): string
    {
        return Str::random(12);
    }
}

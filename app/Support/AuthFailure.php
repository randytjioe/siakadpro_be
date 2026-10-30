<?php

declare(strict_types=1);

namespace App\Support;

/** Kredensial salah / akun tidak boleh masuk (401). Pesan sengaja generik. */
final class AuthFailure extends DomainException
{
    public function status(): int
    {
        return 401;
    }
}

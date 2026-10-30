<?php

declare(strict_types=1);

namespace App\Support;

/** Konflik keadaan: sudah berjalan, sudah terkunci, status tidak mengizinkan (409). */
final class Conflict extends DomainException
{
    public function status(): int
    {
        return 409;
    }
}

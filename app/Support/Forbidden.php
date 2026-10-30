<?php

declare(strict_types=1);

namespace App\Support;

/** Dilarang oleh aturan hak akses di dalam Service (403). */
final class Forbidden extends DomainException
{
    public function status(): int
    {
        return 403;
    }
}

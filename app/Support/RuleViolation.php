<?php

declare(strict_types=1);

namespace App\Support;

/** Aturan bisnis/validasi domain dilanggar (422). */
final class RuleViolation extends DomainException
{
    public function status(): int
    {
        return 422;
    }
}

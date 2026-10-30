<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Konteks permintaan (IP) untuk jejak audit. Diisi middleware; Service tidak menyentuh request()
 * (rules/03). Terdaftar scoped di container.
 */
final class RequestContext
{
    public ?string $ip = null;
}

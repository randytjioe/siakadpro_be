<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * Galat aturan bisnis bertipe. Diterjemahkan ke HTTP di satu tempat (bootstrap/app.php):
 * RuleViolation => 422, Conflict => 409.
 */
abstract class DomainException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($message);
    }

    abstract public function status(): int;
}

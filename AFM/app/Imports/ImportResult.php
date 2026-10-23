<?php

namespace App\Imports;

/**
 * Outcome of a Batch Add: either everything was added (vehicle types and QR
 * cards together), or nothing was and the errors say why.
 */
final class ImportResult
{
    /**
     * @param  list<array{sheet: string, row: int, column: string, message: string}>  $errors
     */
    public function __construct(
        public readonly int $vehicles = 0,
        public readonly int $cards = 0,
        public readonly array $errors = [],
    ) {}

    public function failed(): bool
    {
        return $this->errors !== [];
    }
}

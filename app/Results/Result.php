<?php

namespace App\Results;

abstract class Result
{
    public function __construct(
        protected readonly bool $successful,
        public readonly ?string $failureReason = null,
    ) {}

    public function succeeded(): bool
    {
        return $this->successful;
    }

    public function failed(): bool
    {
        return ! $this->successful;
    }
}

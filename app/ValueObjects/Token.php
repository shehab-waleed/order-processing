<?php

namespace App\ValueObjects;

use App\Enums\TokenType;

final class Token
{
    public function __construct(
        public readonly string $value,
        public readonly TokenType $type,
    ) {}
}

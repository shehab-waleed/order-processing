<?php

namespace App\Results\Auth;

use App\Models\User;
use App\Results\Result;
use App\ValueObjects\Token;

final class LoginResult extends Result
{
    private function __construct(
        bool $successful,
        public readonly ?User $user = null,
        public readonly ?Token $token = null,
        public readonly ?Token $refreshToken = null,
        ?string $failureReason = null,
    ) {
        parent::__construct($successful, $failureReason);
    }

    public static function success(User $user, Token $token, Token $refreshToken): self
    {
        return new self(true, $user, $token, $refreshToken);
    }

    public static function invalidCredentials(): self
    {
        return new self(false, failureReason: 'Invalid credentials');
    }
}

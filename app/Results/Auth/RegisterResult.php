<?php

namespace App\Results\Auth;

use App\Models\User;
use App\Results\Result;
use App\ValueObjects\Token;

final class RegisterResult extends Result
{
    public function __construct(
        public readonly User $user,
        public readonly Token $token,
        public readonly Token $refreshToken,
    ) {
        parent::__construct(true);
    }
}

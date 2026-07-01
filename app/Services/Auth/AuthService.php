<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;
use App\Enums\TokenType;
use App\Models\User;
use App\Results\Auth\LoginResult;
use App\Results\Auth\RegisterResult;
use App\ValueObjects\Token;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthService
{
    public function register(RegisterData $data): RegisterResult
    {
        $user = User::register($data);

        return new RegisterResult(
            $user,
            new Token($user->issueToken(), TokenType::Bearer),
            new Token($user->issueRefreshToken(), TokenType::Bearer),
        );
    }

    public function login(LoginData $data): LoginResult
    {
        $rawToken = JWTAuth::attempt($data->credentials());

        if ($rawToken === false) {
            return LoginResult::failedDueInvalidCredentials();
        }

        $user = User::where('email', $data->email)->firstOrFail();

        return LoginResult::success(
            $user,
            new Token($rawToken, TokenType::Bearer),
            new Token($user->issueRefreshToken(), TokenType::Bearer),
        );
    }
}

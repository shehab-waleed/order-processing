<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginData::fromRequest($request));

        if ($result->failed()) {
            return ApiResponse::unauthorized($result->failureReason ?? 'Unauthorized');
        }

        $token = $result->token;
        $refreshToken = $result->refreshToken;
        assert($token !== null);
        assert($refreshToken !== null);

        return ApiResponse::success('Login successful', [
            'user' => new UserResource($result->user),
            'token' => $token->value,
            'refresh_token' => $refreshToken->value,
            'token_type' => $token->type->value,
        ]);
    }
}

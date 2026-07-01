<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;

final class RegisterController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(RegisterData::fromRequest($request));

        return ApiResponse::created('User registered successfully', [
            'user' => new UserResource($result->user),
            'token' => $result->token->value,
            'refresh_token' => $result->refreshToken->value,
            'token_type' => $result->token->type->value,
        ]);
    }
}

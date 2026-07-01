<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\DTOs\Payment\PaymentFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\IndexPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function __invoke(IndexPaymentRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->paymentService->list($user, PaymentFilterData::fromRequest($request));

        return ApiResponse::success(
            'Payments retrieved successfully',
            $result->payments,
            PaymentResource::class,
        );
    }
}

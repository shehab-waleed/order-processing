<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\DTOs\Payment\PaymentFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\IndexPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class OrderPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function __invoke(IndexPaymentRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $result = $this->paymentService->listForOrder($order, PaymentFilterData::fromRequest($request));

        return ApiResponse::success(
            'Payments retrieved successfully',
            $result->payments,
            PaymentResource::class,
        );
    }
}

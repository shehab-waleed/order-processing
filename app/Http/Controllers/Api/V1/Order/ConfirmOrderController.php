<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\DTOs\Order\ConfirmOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ConfirmOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ConfirmOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(ConfirmOrderRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        $result = $this->orderService->confirm($order, ConfirmOrderData::fromRequest($request));

        if ($result->failed()) {
            return ApiResponse::send(Response::HTTP_CONFLICT, $result->failureReason ?? 'Conflict');
        }

        $payment = $result->payment;
        assert($payment !== null);

        return ApiResponse::success('Order confirmed successfully', [
            'order' => new OrderResource($order->load('items')),
            'payment' => new PaymentResource($payment),
        ]);
    }
}

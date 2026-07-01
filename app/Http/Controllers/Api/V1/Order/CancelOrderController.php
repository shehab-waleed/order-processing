<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class CancelOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        $result = $this->orderService->cancel($order);

        if ($result->failed()) {
            return ApiResponse::send(Response::HTTP_CONFLICT, $result->failureReason ?? 'Conflict');
        }

        return ApiResponse::success('Order cancelled successfully', [
            'order' => new OrderResource($order->load('items')),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\DTOs\Order\OrderData;
use App\DTOs\Order\OrderFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\OrderService;
use App\Util\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(IndexOrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->orderService->list($user, OrderFilterData::fromRequest($request));

        return ApiResponse::success(
            'Orders retrieved successfully',
            $result->orders,
            OrderResource::class,
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->orderService->create($user, OrderData::fromRequest($request));

        return ApiResponse::created('Order created successfully', [
            'order' => new OrderResource($result->order->load('items')),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        $result = $this->orderService->update($order, OrderData::fromRequest($request));

        if ($result->failed()) {
            return ApiResponse::send(Response::HTTP_CONFLICT, $result->failureReason ?? 'Conflict');
        }

        return ApiResponse::success('Order updated successfully', [
            'order' => new OrderResource($order->load('items')),
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        Gate::authorize('delete', $order);

        $result = $this->orderService->delete($order);

        if ($result->failed()) {
            return ApiResponse::send(Response::HTTP_CONFLICT, $result->failureReason ?? 'Conflict');
        }

        return ApiResponse::success('Order deleted successfully');
    }
}

<?php

namespace App\Services\Order;

use App\DTOs\Order\ConfirmOrderData;
use App\DTOs\Order\OrderData;
use App\DTOs\Order\OrderFilterData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Results\Order\CancelOrderResult;
use App\Results\Order\ConfirmOrderResult;
use App\Results\Order\CreateOrderResult;
use App\Results\Order\DeleteOrderResult;
use App\Results\Order\ListOrdersResult;
use App\Results\Order\UpdateOrderResult;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    public function __construct(private readonly PaymentProcessorFactory $paymentProcessorFactory) {}

    public function create(User $user, OrderData $data): CreateOrderResult
    {
        return new CreateOrderResult(Order::place($user, $data));
    }

    public function update(Order $order, OrderData $data): UpdateOrderResult
    {
        if (! $order->isPending()) {
            return UpdateOrderResult::failedDueNotEditable();
        }

        $order->replaceItems($data);

        return UpdateOrderResult::success($order);
    }

    public function confirm(Order $order, ConfirmOrderData $data): ConfirmOrderResult
    {
        if (! $order->isPending()) {
            return ConfirmOrderResult::failedDueNotConfirmable();
        }

        $payment = DB::transaction(function () use ($order, $data): Payment {
            $order->transitionTo(OrderStatus::Confirmed);

            return $this->paymentProcessorFactory->make($data->method)->process($order);
        });

        return ConfirmOrderResult::success($order, $payment);
    }

    public function cancel(Order $order): CancelOrderResult
    {
        if ($order->isCancelled()) {
            return CancelOrderResult::failedDueNotCancellable();
        }

        $order->transitionTo(OrderStatus::Cancelled);

        return CancelOrderResult::success($order);
    }

    public function list(User $user, OrderFilterData $data): ListOrdersResult
    {
        $query = Order::query()
            ->where('user_id', $user->id)
            ->with('items')
            ->latest();

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        return new ListOrdersResult($query->paginate($data->perPage));
    }

    public function delete(Order $order): DeleteOrderResult
    {
        if ($order->hasPayments()) {
            return DeleteOrderResult::failedDueHasAssociatedPayments();
        }

        $order->delete();

        return DeleteOrderResult::success();
    }
}

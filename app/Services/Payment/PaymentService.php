<?php

namespace App\Services\Payment;

use App\DTOs\Payment\PaymentFilterData;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Results\Payment\ListPaymentsResult;
use Illuminate\Contracts\Database\Eloquent\Builder;

final class PaymentService
{
    public function list(User $user, PaymentFilterData $data): ListPaymentsResult
    {
        $query = Payment::query()
            ->whereHas('order', fn (Builder $query): Builder => $query->where('user_id', $user->id))
            ->latest();

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        return new ListPaymentsResult($query->paginate($data->perPage));
    }

    public function listForOrder(Order $order, PaymentFilterData $data): ListPaymentsResult
    {
        $query = $order->payments()->latest();

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        return new ListPaymentsResult($query->paginate($data->perPage));
    }
}

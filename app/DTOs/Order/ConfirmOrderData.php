<?php

namespace App\DTOs\Order;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;

final class ConfirmOrderData
{
    public function __construct(
        public readonly PaymentMethod $method,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            method: PaymentMethod::from($request->string('payment_method')->toString()),
        );
    }
}

<?php

namespace App\Results\Order;

use App\Models\Order;
use App\Results\Result;

final class CancelOrderResult extends Result
{
    private function __construct(
        bool $successful,
        public readonly ?Order $order = null,
        ?string $failureReason = null,
    ) {
        parent::__construct($successful, $failureReason);
    }

    public static function success(Order $order): self
    {
        return new self(true, $order);
    }

    public static function failedDueNotCancellable(): self
    {
        return new self(false, failureReason: 'Order is already cancelled');
    }
}

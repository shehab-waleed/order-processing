<?php

namespace App\Results\Order;

use App\Models\Order;
use App\Models\Payment;
use App\Results\Result;

final class ConfirmOrderResult extends Result
{
    private function __construct(
        bool $successful,
        public readonly ?Order $order = null,
        public readonly ?Payment $payment = null,
        ?string $failureReason = null,
    ) {
        parent::__construct($successful, $failureReason);
    }

    public static function success(Order $order, Payment $payment): self
    {
        return new self(true, $order, $payment);
    }

    public static function failedDueNotConfirmable(): self
    {
        return new self(false, failureReason: 'Only pending orders can be confirmed');
    }
}

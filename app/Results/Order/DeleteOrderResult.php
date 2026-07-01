<?php

namespace App\Results\Order;

use App\Results\Result;

final class DeleteOrderResult extends Result
{
    private function __construct(
        bool $successful,
        ?string $failureReason = null,
    ) {
        parent::__construct($successful, $failureReason);
    }

    public static function success(): self
    {
        return new self(true);
    }

    public static function failedDueHasAssociatedPayments(): self
    {
        return new self(false, 'Orders with associated payments cannot be deleted');
    }
}

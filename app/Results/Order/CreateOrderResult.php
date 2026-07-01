<?php

namespace App\Results\Order;

use App\Models\Order;
use App\Results\Result;

final class CreateOrderResult extends Result
{
    public function __construct(
        public readonly Order $order,
    ) {
        parent::__construct(true);
    }
}

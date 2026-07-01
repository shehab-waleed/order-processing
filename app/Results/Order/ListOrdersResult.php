<?php

namespace App\Results\Order;

use App\Models\Order;
use App\Results\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListOrdersResult extends Result
{
    /**
     * @param  LengthAwarePaginator<int, Order>  $orders
     */
    public function __construct(
        public readonly LengthAwarePaginator $orders,
    ) {
        parent::__construct(true);
    }
}

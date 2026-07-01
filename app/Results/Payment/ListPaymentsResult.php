<?php

namespace App\Results\Payment;

use App\Models\Payment;
use App\Results\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPaymentsResult extends Result
{
    /**
     * @param  LengthAwarePaginator<int, Payment>  $payments
     */
    public function __construct(
        public readonly LengthAwarePaginator $payments,
    ) {
        parent::__construct(true);
    }
}

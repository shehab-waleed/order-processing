<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

interface PaymentProcessor
{
    public function process(Order $order): Payment;
}

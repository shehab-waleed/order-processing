<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;

class PaymentProcessorFactory
{
    public function make(PaymentMethod $method): PaymentProcessor
    {
        return match ($method) {
            PaymentMethod::CreditCard => new CreditCardPaymentProcessor,
        };
    }
}

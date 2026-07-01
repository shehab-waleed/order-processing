<?php

use App\Enums\PaymentMethod;
use App\Services\Payment\CreditCardPaymentProcessor;
use App\Services\Payment\PaymentProcessorFactory;

it('makes the credit card processor for the credit card method', function () {
    $processor = (new PaymentProcessorFactory)->make(PaymentMethod::CreditCard);

    expect($processor)->toBeInstanceOf(CreditCardPaymentProcessor::class);
});

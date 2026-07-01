<?php

namespace App\Exceptions;

use App\Enums\PaymentGateway;
use RuntimeException;

final class MissingGatewayConfigurationException extends RuntimeException
{
    public function __construct(PaymentGateway $gateway)
    {
        parent::__construct("Missing configuration for payment gateway [{$gateway->value}].");
    }
}

<?php

use App\Enums\PaymentGateway;

return [
    'gateways' => [
        PaymentGateway::CreditCard->value => [
            'api_key' => env('CREDIT_CARD_API_KEY'),
            'secret' => env('CREDIT_CARD_SECRET'),
        ],
    ],
];

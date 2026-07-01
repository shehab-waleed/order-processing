<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\MissingGatewayConfigurationException;
use App\Models\Order;
use App\Models\Payment;

final class CreditCardPaymentProcessor implements PaymentProcessor
{
    public readonly string $apiKey;

    public readonly string $secret;

    public function __construct()
    {
        $gateway = PaymentGateway::CreditCard;
        $apiKey = config("payment.gateways.{$gateway->value}.api_key");
        $secret = config("payment.gateways.{$gateway->value}.secret");

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($secret) || $secret === '') {
            throw new MissingGatewayConfigurationException($gateway);
        }

        $this->apiKey = $apiKey;
        $this->secret = $secret;
    }

    public function process(Order $order): Payment
    {
        $payment = new Payment([
            'status' => PaymentStatus::Pending,
            'payment_method' => PaymentMethod::CreditCard,
            'amount' => $order->total,
        ]);

        $order->payments()->save($payment);

        return $payment;
    }
}

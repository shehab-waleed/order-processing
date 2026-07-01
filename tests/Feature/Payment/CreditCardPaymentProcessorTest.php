<?php

use App\Enums\PaymentStatus;
use App\Exceptions\MissingGatewayConfigurationException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\CreditCardPaymentProcessor;

it('reads its credentials from config', function () {
    $processor = new CreditCardPaymentProcessor;

    expect($processor->apiKey)->toBe('cc_test_key')
        ->and($processor->secret)->toBe('cc_test_secret');
});

it('records a pending payment for the order', function () {
    $order = Order::factory()->create();

    $payment = (new CreditCardPaymentProcessor)->process($order);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->status)->toBe(PaymentStatus::Pending);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'pending',
        'payment_method' => 'credit_card',
    ]);
});

it('throws when gateway configuration is missing', function (string $key) {
    config(["payment.gateways.credit_card.{$key}" => null]);

    expect(fn () => new CreditCardPaymentProcessor)
        ->toThrow(MissingGatewayConfigurationException::class);
})->with(['api_key', 'secret']);

<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\User;
use App\Services\Payment\CreditCardPaymentProcessor;
use App\Services\Payment\PaymentProcessorFactory;

it('confirms a pending order and records a payment', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->pending()->create();

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/confirm", [
        'payment_method' => 'credit_card',
    ])->assertOk()
        ->assertJsonPath('data.order.status', 'confirmed')
        ->assertJsonPath('data.payment.status', 'pending')
        ->assertJsonPath('data.payment.payment_method', 'credit_card');

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'pending',
        'payment_method' => 'credit_card',
    ]);
});

it('resolves the processor from the factory by payment method', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->pending()->create();

    $this->mock(PaymentProcessorFactory::class)
        ->shouldReceive('make')
        ->once()
        ->with(PaymentMethod::CreditCard)
        ->andReturn(new CreditCardPaymentProcessor);

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/confirm", [
        'payment_method' => 'credit_card',
    ])->assertOk();
});

it('rejects confirming a non-pending order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->confirmed()->create();

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/confirm", [
        'payment_method' => 'credit_card',
    ])->assertStatus(409);
});

it('rejects confirming with an invalid payment method', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->pending()->create();

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/confirm", [
        'payment_method' => 'bitcoin',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['payment_method']);
});

it('forbids confirming an order owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->pending()->create();

    $this->actingAs($other, 'api')->postJson("/api/v1/orders/{$order->id}/confirm", [
        'payment_method' => 'credit_card',
    ])->assertForbidden();
});

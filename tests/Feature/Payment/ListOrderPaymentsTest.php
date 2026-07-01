<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

it('lists payments for a specific order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();
    Payment::factory()->for($order)->count(2)->create();
    Payment::factory()->for(Order::factory()->for($user))->create();

    $this->actingAs($user, 'api')->getJson("/api/v1/orders/{$order->id}/payments")
        ->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'data' => [['id', 'order_id', 'status', 'payment_method', 'amount']],
        ])
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.order_id', $order->id);
});

it('filters order payments by status', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();
    Payment::factory()->for($order)->count(2)->create();
    Payment::factory()->for($order)->failed()->create();

    $this->actingAs($user, 'api')->getJson("/api/v1/orders/{$order->id}/payments?status=failed")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.status', 'failed');
});

it('forbids viewing payments of an order owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $this->actingAs($other, 'api')->getJson("/api/v1/orders/{$order->id}/payments")
        ->assertForbidden();
});

it('requires authentication to list order payments', function () {
    $order = Order::factory()->create();

    $this->getJson("/api/v1/orders/{$order->id}/payments")->assertUnauthorized();
});

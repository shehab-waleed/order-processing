<?php

use App\Models\Order;
use App\Models\User;

it('updates items on a pending order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->pending()->create();

    $this->actingAs($user, 'api')->putJson("/api/v1/orders/{$order->id}", [
        'items' => [['product_name' => 'Replacement', 'quantity' => 3, 'price' => 10.00]],
    ])->assertOk()
        ->assertJsonPath('data.order.total', '30.00');

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_name' => 'Replacement',
        'quantity' => 3,
    ]);
});

it('rejects updating a confirmed order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->confirmed()->create();

    $this->actingAs($user, 'api')->putJson("/api/v1/orders/{$order->id}", [
        'items' => [['product_name' => 'Replacement', 'quantity' => 1, 'price' => 10.00]],
    ])->assertStatus(409);
});

it('forbids updating an order owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->pending()->create();

    $this->actingAs($other, 'api')->putJson("/api/v1/orders/{$order->id}", [
        'items' => [['product_name' => 'Replacement', 'quantity' => 1, 'price' => 10.00]],
    ])->assertForbidden();
});

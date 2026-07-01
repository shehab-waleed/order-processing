<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

it('deletes an order without payments', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $this->actingAs($user, 'api')->deleteJson("/api/v1/orders/{$order->id}")
        ->assertOk();

    $this->assertDatabaseMissing('orders', ['id' => $order->id]);
});

it('rejects deleting an order with associated payments', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->confirmed()->create();
    Payment::factory()->for($order)->create();

    $this->actingAs($user, 'api')->deleteJson("/api/v1/orders/{$order->id}")
        ->assertStatus(409);

    $this->assertDatabaseHas('orders', ['id' => $order->id]);
});

it('forbids deleting an order owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $this->actingAs($other, 'api')->deleteJson("/api/v1/orders/{$order->id}")
        ->assertForbidden();
});

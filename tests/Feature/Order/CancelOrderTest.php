<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

it('cancels a pending order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->pending()->create();

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertOk()
        ->assertJsonPath('data.order.status', 'cancelled');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('rejects cancelling an already cancelled order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->cancelled()->create();

    $this->actingAs($user, 'api')->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(409);
});

it('forbids cancelling an order owned by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->pending()->create();

    $this->actingAs($other, 'api')->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertForbidden();
});

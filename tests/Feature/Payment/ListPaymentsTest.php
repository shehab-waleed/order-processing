<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

it('lists payments scoped to the authenticated user', function () {
    $user = User::factory()->create();
    Payment::factory()->for(Order::factory()->for($user))->count(2)->create();
    Payment::factory()->for(Order::factory()->for(User::factory()))->count(3)->create();

    $this->actingAs($user, 'api')->getJson('/api/v1/payments')
        ->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'data' => [['id', 'order_id', 'status', 'payment_method', 'amount']],
        ])
        ->assertJsonPath('meta.total', 2);
});

it('filters payments by status', function () {
    $user = User::factory()->create();
    Payment::factory()->for(Order::factory()->for($user))->count(2)->create();
    Payment::factory()->for(Order::factory()->for($user))->successful()->create();

    $this->actingAs($user, 'api')->getJson('/api/v1/payments?status=successful')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.status', 'successful');
});

it('requires authentication to list payments', function () {
    $this->getJson('/api/v1/payments')->assertUnauthorized();
});

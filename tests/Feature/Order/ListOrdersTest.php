<?php

use App\Models\Order;
use App\Models\User;

it('lists orders scoped to the authenticated user', function () {
    $user = User::factory()->create();
    Order::factory()->for($user)->count(2)->create();
    Order::factory()->for(User::factory())->count(3)->create();

    $this->actingAs($user, 'api')->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'data' => [['id', 'status', 'total']],
        ])
        ->assertJsonPath('meta.total', 2);
});

it('filters orders by status', function () {
    $user = User::factory()->create();
    Order::factory()->for($user)->pending()->count(2)->create();
    Order::factory()->for($user)->confirmed()->create();

    $this->actingAs($user, 'api')->getJson('/api/v1/orders?status=confirmed')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.status', 'confirmed');
});

it('requires authentication to list orders', function () {
    $this->getJson('/api/v1/orders')->assertUnauthorized();
});

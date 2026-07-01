<?php

use App\Models\Order;
use App\Models\User;

it('creates an order and calculates the total', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')->postJson('/api/v1/orders', [
        'items' => [
            ['product_name' => 'Widget', 'quantity' => 2, 'price' => 9.99],
            ['product_name' => 'Gadget', 'quantity' => 1, 'price' => 5.00],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'order' => [
                    'id',
                    'status',
                    'total',
                    'items' => [['id', 'product_name', 'quantity', 'price_per_item', 'subtotal']],
                ],
            ],
        ])
        ->assertJsonPath('data.order.status', 'pending')
        ->assertJsonPath('data.order.total', '24.98');

    expect(Order::where('user_id', $user->id)->count())->toBe(1);
    $this->assertDatabaseCount('order_items', 2);
});

it('requires authentication to create an order', function () {
    $this->postJson('/api/v1/orders', [
        'items' => [['product_name' => 'Widget', 'quantity' => 1, 'price' => 5.00]],
    ])->assertUnauthorized();
});

it('rejects an order with no items', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')->postJson('/api/v1/orders', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

it('rejects an order item with invalid fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')->postJson('/api/v1/orders', [
        'items' => [['product_name' => '', 'quantity' => 0, 'price' => -1]],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors([
            'items.0.product_name',
            'items.0.quantity',
            'items.0.price',
        ]);
});

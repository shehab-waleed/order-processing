<?php

use App\Models\User;

it('registers a user and returns a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Sam',
        'email' => 'sam@example.com',
        'password' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'created_at'],
                'token',
                'refresh_token',
                'token_type',
            ],
        ]);

    expect(User::where('email', 'sam@example.com')->exists())->toBeTrue();
});

it('rejects registration with missing fields', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects registration with a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Sam',
        'email' => 'taken@example.com',
        'password' => 'password123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

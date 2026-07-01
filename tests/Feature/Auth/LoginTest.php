<?php

use App\Models\User;

it('logs in with valid credentials and returns a token', function () {
    User::factory()->create([
        'email' => 'sam@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'sam@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'refresh_token',
                'token_type',
            ],
        ]);
});

it('rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'sam@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'sam@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnauthorized()
        ->assertJson(['message' => 'Invalid credentials']);
});

it('rejects login with missing fields', function () {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

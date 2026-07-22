<?php

use App\Models\User;

test('guest user probe returns a null user', function () {
    $this->getJson('/api/user')
        ->assertOk()
        ->assertExactJson(['user' => null]);
});

test('authenticated user probe returns the current user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'teacher',
    ]);

    $this->actingAs($user)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.name', 'Test User')
        ->assertJsonPath('user.email', 'test@example.com')
        ->assertJsonPath('user.role', 'teacher');
});

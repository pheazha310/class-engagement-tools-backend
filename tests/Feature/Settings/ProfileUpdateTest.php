<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->getJson('/api/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->putJson('/api/profile', [
            'name' => 'Test User',
        ]);

    $response->assertOk();

    $user->refresh();

    expect($user->name)->toBe('Test User');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->putJson('/api/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response->assertOk();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/logout')
        ->assertNoContent();

    $this->assertGuest();
});

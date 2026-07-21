<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('api registration creates a missing selected role before assigning it', function () {
    expect(Role::where('name', 'student')->exists())->toBeFalse();

    $this->postJson('/api/auth/register', [
        'name' => 'Register User',
        'email' => 'register@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
    ])
        ->assertCreated()
        ->assertJsonPath('user.email', 'register@example.com');

    $user = User::where('email', 'register@example.com')->firstOrFail();

    expect(Role::where('name', 'student')->where('guard_name', 'web')->exists())->toBeTrue()
        ->and($user->hasRole('student'))->toBeTrue();
});

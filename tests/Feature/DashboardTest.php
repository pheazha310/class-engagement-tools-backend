<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guests are redirected to the admin dashboard', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect('/admin/dashboard');
});

test('authenticated admins can visit the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    $response = $this->followingRedirects()->get(route('dashboard'));
    $response->assertOk();
});

test('non-admin users are forbidden from the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create(['role' => 'student']);
    $this->actingAs($user);

    $response = $this->followingRedirects()->get(route('dashboard'));
    $response->assertForbidden();
});

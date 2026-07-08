<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('forbids non-admins from the user list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
});

it('redirects guests to login', function () {
    $this->get('/admin/users')->assertRedirect(route('login'));
});

it('lets an admin view the user list', function () {
    $this->actingAs($this->admin)
        ->get('/admin/users')
        ->assertOk();
});

it('filters the user list by search term', function () {
    User::factory()->create(['name' => 'Findable Person']);
    User::factory()->create(['name' => 'Someone Else']);

    $this->actingAs($this->admin)
        ->get('/admin/users?search=Findable')
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Findable Person'));
});

it('creates a user with roles', function () {
    $this->actingAs($this->admin)->post('/admin/users', [
        'name' => 'New Teacher',
        'email' => 'teacher@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => ['teacher'],
    ])->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'teacher@example.com')->firstOrFail();

    expect($user->hasRole('teacher'))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
});

it('validates the user create form', function () {
    $this->actingAs($this->admin)->post('/admin/users', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'roles' => ['nonexistent-role'],
    ])->assertSessionHasErrors(['name', 'email', 'password', 'roles.0']);
});

it('updates a user and syncs roles', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $user->assignRole('teacher');

    $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
        'name' => 'New Name',
        'email' => $user->email,
        'password' => '',
        'roles' => ['student'],
    ])->assertRedirect(route('admin.users.index'));

    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->hasRole('student'))->toBeTrue();
    expect($user->hasRole('teacher'))->toBeFalse();
});

it('leaves the password unchanged when left blank on update', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
        'name' => $user->name,
        'email' => $user->email,
        'password' => '',
        'roles' => [],
    ]);

    expect($user->fresh()->password)->toBe($originalHash);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/users/{$user->id}")
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($user->id))->toBeNull();
});

it('prevents an admin from deleting their own account', function () {
    $this->actingAs($this->admin)
        ->delete("/admin/users/{$this->admin->id}");

    expect(User::find($this->admin->id))->not->toBeNull();
});

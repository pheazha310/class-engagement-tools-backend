<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('forbids non-admins from the role list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/admin/roles')
        ->assertForbidden();
});

it('lets an admin view the role list', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/admin/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates a role with permissions', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/roles', [
        'name' => 'moderator',
        'permissions' => ['view users', 'view reports'],
    ])->assertCreated();

    $role = Role::findByName('moderator', 'web');

    expect($role->hasPermissionTo('view users', 'web'))->toBeTrue();
    expect($role->hasPermissionTo('view reports', 'web'))->toBeTrue();
});

it('rejects a duplicate role name', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/roles', [
        'name' => 'teacher',
        'permissions' => [],
    ])->assertUnprocessable();
});

it('rejects unknown permissions', function () {
    $this->actingAs($this->admin)->postJson('/api/admin/roles', [
        'name' => 'moderator',
        'permissions' => ['not-a-real-permission'],
    ])->assertUnprocessable();
});

it('updates a role and syncs permissions', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $this->actingAs($this->admin)->putJson("/api/admin/roles/{$role->id}", [
        'name' => 'moderator-renamed',
        'permissions' => ['manage classes'],
    ])->assertOk();

    $role->refresh();

    expect($role->name)->toBe('moderator-renamed');
    expect($role->hasPermissionTo('manage classes', 'web'))->toBeTrue();
});

it('does not rename the protected admin role but still syncs its permissions', function () {
    $adminRole = Role::findByName('admin', 'web');

    $this->actingAs($this->admin)->putJson("/api/admin/roles/{$adminRole->id}", [
        'name' => 'super',
        'permissions' => ['view users'],
    ])->assertForbidden();

    $adminRole->refresh();

    expect($adminRole->name)->toBe('admin');
});

it('deletes a non-protected role', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $this->actingAs($this->admin)
        ->deleteJson("/api/admin/roles/{$role->id}")
        ->assertOk();

    expect(Role::find($role->id))->toBeNull();
});

it('refuses to delete the protected admin role', function () {
    $adminRole = Role::findByName('admin', 'web');

    $this->actingAs($this->admin)
        ->deleteJson("/api/admin/roles/{$adminRole->id}")
        ->assertForbidden();

    expect(Role::where('id', $adminRole->id)->exists())->toBeTrue();
});

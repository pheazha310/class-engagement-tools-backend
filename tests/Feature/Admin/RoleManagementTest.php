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

    $this->actingAs($user)->get('/admin/roles')->assertForbidden();
});

it('lets an admin view the role list', function () {
    $this->actingAs($this->admin)
        ->get('/admin/roles')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/roles/Index')
            ->has('roles', 3));
});

it('creates a role with permissions', function () {
    $this->actingAs($this->admin)->post('/admin/roles', [
        'name' => 'moderator',
        'permissions' => ['view users', 'view reports'],
    ])->assertRedirect(route('admin.roles.index'));

    $role = Role::findByName('moderator');

    expect($role->hasPermissionTo('view users'))->toBeTrue();
    expect($role->hasPermissionTo('view reports'))->toBeTrue();
});

it('rejects a duplicate role name', function () {
    $this->actingAs($this->admin)->post('/admin/roles', [
        'name' => 'teacher',
        'permissions' => [],
    ])->assertSessionHasErrors('name');
});

it('rejects unknown permissions', function () {
    $this->actingAs($this->admin)->post('/admin/roles', [
        'name' => 'moderator',
        'permissions' => ['not-a-real-permission'],
    ])->assertSessionHasErrors('permissions.0');
});

it('updates a role and syncs permissions', function () {
    $role = Role::create(['name' => 'moderator']);

    $this->actingAs($this->admin)->put("/admin/roles/{$role->id}", [
        'name' => 'moderator-renamed',
        'permissions' => ['manage classes'],
    ])->assertRedirect(route('admin.roles.index'));

    $role->refresh();

    expect($role->name)->toBe('moderator-renamed');
    expect($role->hasPermissionTo('manage classes'))->toBeTrue();
});

it('does not rename the protected admin role but still syncs its permissions', function () {
    $admin = Role::findByName('admin');

    $this->actingAs($this->admin)->put("/admin/roles/{$admin->id}", [
        'name' => 'super',
        'permissions' => ['view users'],
    ]);

    $admin->refresh();

    expect($admin->name)->toBe('admin');
    expect($admin->permissions)->toHaveCount(1);
});

it('deletes a non-protected role', function () {
    $role = Role::create(['name' => 'moderator']);

    $this->actingAs($this->admin)
        ->delete("/admin/roles/{$role->id}")
        ->assertRedirect(route('admin.roles.index'));

    expect(Role::find($role->id))->toBeNull();
});

it('refuses to delete the protected admin role', function () {
    $admin = Role::findByName('admin');

    $this->actingAs($this->admin)
        ->delete("/admin/roles/{$admin->id}");

    expect(Role::find($admin->id))->not->toBeNull();
});

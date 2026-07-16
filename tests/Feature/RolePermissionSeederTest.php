<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates the admin, teacher, and student roles', function () {
    expect(Role::pluck('name'))->toContain('admin', 'teacher', 'student');
});

it('grants every permission to the admin role', function () {
    $admin = Role::findByName('admin', 'web');

    expect($admin->permissions()->count())->toBe(Permission::count());
});

it('grants teachers a scoped subset of permissions', function () {
    $teacher = Role::findByName('teacher', 'web');

    expect($teacher->hasPermissionTo('manage classes', 'web'))->toBeTrue();
    expect($teacher->hasPermissionTo('manage users', 'web'))->toBeFalse();
});

it('grants students only their own permissions', function () {
    $student = Role::findByName('student', 'web');

    expect($student->hasPermissionTo('join engagements', 'web'))->toBeTrue();
    expect($student->hasPermissionTo('manage classes', 'web'))->toBeFalse();
});

it('is idempotent and does not duplicate roles or permissions', function () {
    $rolesBefore = Role::count();
    $permissionsBefore = Permission::count();

    $this->seed(RolePermissionSeeder::class);

    expect(Role::count())->toBe($rolesBefore);
    expect(Permission::count())->toBe($permissionsBefore);
});

it('assigns the admin role to the seeded admin user', function () {
    $this->seed(AdminUserSeeder::class);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();

    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->can('manage users'))->toBeTrue();
});

it('does not duplicate the admin user when seeded twice', function () {
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::where('email', 'admin@example.com')->count())->toBe(1);
});

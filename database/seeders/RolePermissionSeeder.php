<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The permissions granted to each role.
     *
     * A '*' grants every defined permission.
     *
     * @var array<string, list<string>>
     */
    private array $rolePermissions = [
        'admin' => ['*'],
        'teacher' => [
            'view users',
            'manage classes',
            'manage engagements',
            'view reports',
        ],
        'student' => [
            'view classes',
            'join engagements',
        ],
    ];

    /**
     * Every permission the application knows about.
     *
     * @var list<string>
     */
    private array $permissions = [
        'view users',
        'manage users',
        'manage roles',
        'view classes',
        'manage classes',
        'view engagements',
        'manage engagements',
        'join engagements',
        'view reports',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions before seeding.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName);

            $role->syncPermissions(
                $permissions === ['*'] ? Permission::all() : $permissions,
            );
        }
    }
}

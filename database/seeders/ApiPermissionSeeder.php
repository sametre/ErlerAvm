<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ApiPermissionSeeder extends Seeder
{
    /**
     * Seed the granular permissions shared by the panel and management API.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'products.view',
            'products.manage',
            'inventory.manage',
            'categories.manage',
            'orders.view',
            'orders.manage',
            'shipping.manage',
            'customers.view',
            'users.manage',
            'reviews.manage',
            'campaigns.manage',
            'content.manage',
            'settings.manage',
            'api-clients.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(array_values(array_filter(
            $permissions,
            fn (string $permission): bool => $permission !== 'users.manage' && $permission !== 'settings.manage',
        )));
    }
}

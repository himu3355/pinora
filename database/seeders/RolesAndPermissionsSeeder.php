<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // ─────────────────────────────────────────────
        // Define all permissions grouped by domain
        // ─────────────────────────────────────────────
        $permissions = [
            // Vendor Management
            'view_vendor',
            'create_vendor',
            'edit_vendor',
            'approve_vendor',
            'suspend_vendor',
            'delete_vendor',

            // Product Management
            'view_product',
            'create_product',
            'edit_product',
            'delete_product',
            'publish_product',

            // Category Management
            'view_category',
            'create_category',
            'edit_category',
            'delete_category',

            // Metal Rate Management
            'view_metal_rate',
            'create_metal_rate',
            'edit_metal_rate',
            'delete_metal_rate',

            // Order Management
            'view_order',
            'update_order_status',
            'cancel_order',

            // Customer Management
            'view_customer',
            'edit_customer',
            'tag_customer',
            'impersonate_customer',

            // Review Management
            'view_review',
            'approve_review',
            'reject_review',
            'delete_review',

            // Payout Management
            'create_payout',
            'mark_payout_paid',

            // User Management
            'view_user',
            'create_user',
            'edit_user',
            'delete_user',

            // Role Management
            'view_role',
            'create_role',
            'edit_role',
            'delete_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => $guard]
            );
        }

        // ─────────────────────────────────────────────
        // Create Roles
        // ─────────────────────────────────────────────

        // super_admin — bypass handled by Gate::before
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => $guard]
        );
        // No explicit permissions needed; Gate::before grants everything.

        // platform_manager — full access except role management
        $platformManager = Role::firstOrCreate(
            ['name' => 'platform_manager', 'guard_name' => $guard]
        );
        $platformManagerPermissions = array_filter(
            $permissions,
            fn(string $p) => !in_array($p, ['view_role', 'create_role', 'edit_role', 'delete_role'])
        );
        $platformManager->syncPermissions($platformManagerPermissions);

        // vendor — own product/order management
        $vendor = Role::firstOrCreate(
            ['name' => 'vendor', 'guard_name' => $guard]
        );
        $vendor->syncPermissions([
            'view_product',
            'create_product',
            'edit_product',
            'delete_product',
            'publish_product',
            'view_order',
            'update_order_status',
        ]);

        // vendor_staff — limited vendor access
        $vendorStaff = Role::firstOrCreate(
            ['name' => 'vendor_staff', 'guard_name' => $guard]
        );
        $vendorStaff->syncPermissions([
            'view_product',
            'edit_product',
            'view_order',
            'update_order_status',
        ]);

        // customer — no admin/vendor access
        $customer = Role::firstOrCreate(
            ['name' => 'customer', 'guard_name' => $guard]
        );
        $customer->syncPermissions([]);

        $this->command->info('✅ Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permission Count'],
            [
                ['super_admin', 'ALL (Gate::before bypass)'],
                ['platform_manager', count($platformManagerPermissions)],
                ['vendor', 7],
                ['vendor_staff', 4],
                ['customer', 0],
            ]
        );
    }
}

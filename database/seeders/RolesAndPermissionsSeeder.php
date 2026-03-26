<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            
            // Products
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Categories
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            
            // Brands
            'view_brands',
            'create_brands',
            'edit_brands',
            'delete_brands',
            
            // Inventory
            'view_inventory',
            'manage_inventory',
            
            // Sales
            'view_sales',
            'create_sales',
            'edit_sales',
            'delete_sales',
            'cancel_sales',
            
            // Repairs
            'view_repairs',
            'create_repairs',
            'edit_repairs',
            'delete_repairs',
            'manage_repair_status',
            
            // Customers
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            
            // Suppliers
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            
            // Expenses
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            
            // Reports
            'view_reports',
            'export_reports',
            
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Company
            'manage_company',
            
            // Settings
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions

        // Administrator role - all permissions
        $adminRole = Role::create(['name' => 'administrator', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Seller role
        $sellerRole = Role::create(['name' => 'seller', 'guard_name' => 'web']);
        $sellerRole->givePermissionTo([
            'view_dashboard',
            'view_products',
            'view_inventory',
            'view_sales',
            'create_sales',
            'view_repairs',
            'create_repairs',
            'view_customers',
            'create_customers',
            'edit_customers',
        ]);

        // Technician role
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'web']);
        $technicianRole->givePermissionTo([
            'view_dashboard',
            'view_repairs',
            'edit_repairs',
            'manage_repair_status',
            'view_customers',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ExpensePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ver gastos',
            'crear gastos',
            'editar gastos',
            'eliminar gastos',
            'aprobar gastos',
            'gestionar categorias gastos',
            'gestionar proveedores gastos',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($permissions);
        }

        if ($tecnico = Role::where('name', 'tecnico')->first()) {
            $tecnico->givePermissionTo([
                'ver gastos',
                'crear gastos',
            ]);
        }

        if ($vendedor = Role::where('name', 'vendedor')->first()) {
            $vendedor->givePermissionTo([
                'ver gastos',
                'crear gastos',
            ]);
        }
    }
}

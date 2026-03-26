<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $tecnico = Role::firstOrCreate(['name' => 'tecnico']);
        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);

        // Permisos
        $permisos = [
            // Dashboard
            'ver dashboard',
            
            // Clientes
            'ver clientes',
            'crear clientes',
            'editar clientes',
            'eliminar clientes',
            
            // Productos
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',
            
            // Categorías
            'ver categorias',
            'crear categorias',
            'editar categorias',
            'eliminar categorias',
            
            // Ventas
            'ver ventas',
            'crear ventas',
            'cancelar ventas',
            
            // Reparaciones
            'ver reparaciones',
            'crear reparaciones',
            'editar reparaciones',
            'cambiar estado reparaciones',
            
            // Reportes
            'ver reportes',
            
            // Usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
        ];

        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        // Asignar permisos a roles
        // Admin tiene todos los permisos
        $admin->givePermissionTo(Permission::all());

        // Técnico
        $tecnico->givePermissionTo([
            'ver dashboard',
            'ver clientes',
            'crear clientes',
            'ver reparaciones',
            'crear reparaciones',
            'editar reparaciones',
            'cambiar estado reparaciones',
        ]);

        // Vendedor
        $vendedor->givePermissionTo([
            'ver dashboard',
            'ver clientes',
            'crear clientes',
            'ver productos',
            'ver ventas',
            'crear ventas',
        ]);
    }
}

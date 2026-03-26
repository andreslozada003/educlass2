<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@cellfix.com',
            'password' => Hash::make('password'),
            'phone' => '555-0100',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Usuario Técnico
        $tecnico = User::create([
            'name' => 'Técnico Principal',
            'email' => 'tecnico@cellfix.com',
            'password' => Hash::make('password'),
            'phone' => '555-0101',
            'is_active' => true,
        ]);
        $tecnico->assignRole('tecnico');

        // Usuario Vendedor
        $vendedor = User::create([
            'name' => 'Vendedor Principal',
            'email' => 'vendedor@cellfix.com',
            'password' => Hash::make('password'),
            'phone' => '555-0102',
            'is_active' => true,
        ]);
        $vendedor->assignRole('vendedor');

        // Usuarios adicionales
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('vendedor');
        });
    }
}

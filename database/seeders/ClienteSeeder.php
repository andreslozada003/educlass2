<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez García',
                'telefono' => '555-1001',
                'email' => 'juan.perez@email.com',
                'direccion' => 'Calle Principal 123',
                'ciudad' => 'Ciudad de México',
                'activo' => true,
            ],
            [
                'nombre' => 'María',
                'apellido' => 'López Hernández',
                'telefono' => '555-1002',
                'email' => 'maria.lopez@email.com',
                'direccion' => 'Av. Reforma 456',
                'ciudad' => 'Ciudad de México',
                'activo' => true,
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Martínez Rodríguez',
                'telefono' => '555-1003',
                'email' => 'carlos.martinez@email.com',
                'direccion' => 'Calle Hidalgo 789',
                'ciudad' => 'Guadalajara',
                'activo' => true,
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'González Silva',
                'telefono' => '555-1004',
                'email' => 'ana.gonzalez@email.com',
                'direccion' => 'Av. Juárez 321',
                'ciudad' => 'Monterrey',
                'activo' => true,
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Sánchez Torres',
                'telefono' => '555-1005',
                'email' => 'luis.sanchez@email.com',
                'direccion' => 'Calle Morelos 654',
                'ciudad' => 'Puebla',
                'activo' => true,
            ],
            [
                'nombre' => 'Diana',
                'apellido' => 'Ramírez Flores',
                'telefono' => '555-1006',
                'email' => 'diana.ramirez@email.com',
                'direccion' => 'Av. Madero 987',
                'ciudad' => 'Ciudad de México',
                'activo' => true,
            ],
            [
                'nombre' => 'Roberto',
                'apellido' => 'Hernández Cruz',
                'telefono' => '555-1007',
                'email' => 'roberto.hernandez@email.com',
                'direccion' => 'Calle Allende 147',
                'ciudad' => 'Toluca',
                'activo' => true,
            ],
            [
                'nombre' => 'Patricia',
                'apellido' => 'Díaz Morales',
                'telefono' => '555-1008',
                'email' => 'patricia.diaz@email.com',
                'direccion' => 'Av. Insurgentes 258',
                'ciudad' => 'Ciudad de México',
                'activo' => true,
            ],
            [
                'nombre' => 'Fernando',
                'apellido' => 'Ruiz Castro',
                'telefono' => '555-1009',
                'email' => 'fernando.ruiz@email.com',
                'direccion' => 'Calle Zaragoza 369',
                'ciudad' => 'Querétaro',
                'activo' => true,
            ],
            [
                'nombre' => 'Sofía',
                'apellido' => 'Vargas Reyes',
                'telefono' => '555-1010',
                'email' => 'sofia.vargas@email.com',
                'direccion' => 'Av. Universidad 741',
                'ciudad' => 'Ciudad de México',
                'activo' => true,
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Celulares',
                'slug' => 'celulares',
                'description' => 'Teléfonos móviles de todas las marcas',
                'icon' => 'smartphone',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Accesorios',
                'slug' => 'accesorios',
                'description' => 'Accesorios para celulares',
                'icon' => 'headphones',
                'color' => '#10B981',
            ],
            [
                'name' => 'Cargadores',
                'slug' => 'cargadores',
                'description' => 'Cargadores y cables',
                'icon' => 'battery-charging',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Fundas',
                'slug' => 'fundas',
                'description' => 'Fundas y protectores',
                'icon' => 'shield',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Audífonos',
                'slug' => 'audifonos',
                'description' => 'Audífonos y headsets',
                'icon' => 'headphones',
                'color' => '#EC4899',
            ],
            [
                'name' => 'Repuestos',
                'slug' => 'repuestos',
                'description' => 'Repuestos para reparaciones',
                'icon' => 'tool',
                'color' => '#6B7280',
            ],
            [
                'name' => 'Servicios',
                'slug' => 'servicios',
                'description' => 'Servicios técnicos',
                'icon' => 'wrench',
                'color' => '#EF4444',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

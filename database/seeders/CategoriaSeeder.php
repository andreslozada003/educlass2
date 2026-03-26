<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Celulares',
                'slug' => 'celulares',
                'descripcion' => 'Teléfonos móviles nuevos y reacondicionados',
                'icono' => 'mobile-alt',
                'color' => '#3b82f6',
                'orden' => 1,
            ],
            [
                'nombre' => 'Accesorios',
                'slug' => 'accesorios',
                'descripcion' => 'Fundas, protectores, cargadores y más',
                'icono' => 'headphones',
                'color' => '#8b5cf6',
                'orden' => 2,
            ],
            [
                'nombre' => 'Repuestos',
                'slug' => 'repuestos',
                'descripcion' => 'Pantallas, baterías, conectores y componentes',
                'icono' => 'tools',
                'color' => '#f59e0b',
                'orden' => 3,
            ],
            [
                'nombre' => 'Servicios',
                'slug' => 'servicios',
                'descripcion' => 'Servicios de reparación y mantenimiento',
                'icono' => 'wrench',
                'color' => '#10b981',
                'orden' => 4,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        // Subcategorías de Accesorios
        $accesorios = Categoria::where('slug', 'accesorios')->first();
        
        $subcategorias = [
            [
                'nombre' => 'Fundas',
                'slug' => 'fundas',
                'descripcion' => 'Fundas protectores para celulares',
                'icono' => 'mobile',
                'color' => '#8b5cf6',
                'parent_id' => $accesorios->id,
                'orden' => 1,
            ],
            [
                'nombre' => 'Protectores de Pantalla',
                'slug' => 'protectores-pantalla',
                'descripcion' => 'Mica de cristal y plástico',
                'icono' => 'shield-alt',
                'color' => '#8b5cf6',
                'parent_id' => $accesorios->id,
                'orden' => 2,
            ],
            [
                'nombre' => 'Cargadores',
                'slug' => 'cargadores',
                'descripcion' => 'Cargadores de pared y coche',
                'icono' => 'plug',
                'color' => '#8b5cf6',
                'parent_id' => $accesorios->id,
                'orden' => 3,
            ],
            [
                'nombre' => 'Cables',
                'slug' => 'cables',
                'descripcion' => 'Cables USB, Lightning, USB-C',
                'icono' => 'usb',
                'color' => '#8b5cf6',
                'parent_id' => $accesorios->id,
                'orden' => 4,
            ],
            [
                'nombre' => 'Audífonos',
                'slug' => 'audifonos',
                'descripcion' => 'Audífonos alámbricos y bluetooth',
                'icono' => 'headphones',
                'color' => '#8b5cf6',
                'parent_id' => $accesorios->id,
                'orden' => 5,
            ],
        ];

        foreach ($subcategorias as $subcategoria) {
            Categoria::create($subcategoria);
        }
    }
}

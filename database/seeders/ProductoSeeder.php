<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $celulares = Categoria::where('slug', 'celulares')->first();
        $accesorios = Categoria::where('slug', 'accesorios')->first();
        $repuestos = Categoria::where('slug', 'repuestos')->first();
        $servicios = Categoria::where('slug', 'servicios')->first();

        // Celulares
        $productosCelulares = [
            [
                'codigo' => 'CEL-SAM-S23-001',
                'nombre' => 'Samsung Galaxy S23',
                'descripcion' => 'Samsung Galaxy S23 128GB Phantom Black',
                'categoria_id' => $celulares->id,
                'marca' => 'Samsung',
                'modelo' => 'Galaxy S23',
                'precio_compra' => 15000,
                'precio_venta' => 18999,
                'stock' => 5,
                'stock_minimo' => 2,
                'tipo' => 'celular',
            ],
            [
                'codigo' => 'CEL-APP-14-001',
                'nombre' => 'iPhone 14',
                'descripcion' => 'iPhone 14 128GB Midnight',
                'categoria_id' => $celulares->id,
                'marca' => 'Apple',
                'modelo' => 'iPhone 14',
                'precio_compra' => 18000,
                'precio_venta' => 22999,
                'stock' => 3,
                'stock_minimo' => 2,
                'tipo' => 'celular',
            ],
            [
                'codigo' => 'CEL-XIA-12-001',
                'nombre' => 'Xiaomi Redmi Note 12',
                'descripcion' => 'Xiaomi Redmi Note 12 128GB Onyx Gray',
                'categoria_id' => $celulares->id,
                'marca' => 'Xiaomi',
                'modelo' => 'Redmi Note 12',
                'precio_compra' => 3500,
                'precio_venta' => 4999,
                'stock' => 10,
                'stock_minimo' => 3,
                'tipo' => 'celular',
            ],
            [
                'codigo' => 'CEL-MOT-G52-001',
                'nombre' => 'Motorola Moto G52',
                'descripcion' => 'Motorola Moto G52 128GB Charcoal Grey',
                'categoria_id' => $celulares->id,
                'marca' => 'Motorola',
                'modelo' => 'Moto G52',
                'precio_compra' => 3200,
                'precio_venta' => 4499,
                'stock' => 8,
                'stock_minimo' => 3,
                'tipo' => 'celular',
            ],
        ];

        // Accesorios
        $productosAccesorios = [
            [
                'codigo' => 'ACC-FUN-IPH14-001',
                'nombre' => 'Funda iPhone 14 Silicone',
                'descripcion' => 'Funda de silicona para iPhone 14',
                'categoria_id' => $accesorios->id,
                'marca' => 'Generic',
                'modelo' => 'iPhone 14',
                'precio_compra' => 80,
                'precio_venta' => 199,
                'stock' => 50,
                'stock_minimo' => 10,
                'tipo' => 'accesorio',
            ],
            [
                'codigo' => 'ACC-MIC-SAM-001',
                'nombre' => 'Mica Cristal Templado Samsung',
                'descripcion' => 'Protector de pantalla de cristal templado universal Samsung',
                'categoria_id' => $accesorios->id,
                'marca' => 'Generic',
                'modelo' => 'Universal',
                'precio_compra' => 25,
                'precio_venta' => 79,
                'stock' => 100,
                'stock_minimo' => 20,
                'tipo' => 'accesorio',
            ],
            [
                'codigo' => 'ACC-CAR-RAP-001',
                'nombre' => 'Cargador Rápido 20W',
                'descripcion' => 'Cargador de pared USB-C 20W carga rápida',
                'categoria_id' => $accesorios->id,
                'marca' => 'Generic',
                'modelo' => '20W',
                'precio_compra' => 60,
                'precio_venta' => 149,
                'stock' => 30,
                'stock_minimo' => 10,
                'tipo' => 'accesorio',
            ],
            [
                'codigo' => 'ACC-CAB-TIP-C-001',
                'nombre' => 'Cable USB-C a Lightning',
                'descripcion' => 'Cable de carga y datos USB-C a Lightning 1m',
                'categoria_id' => $accesorios->id,
                'marca' => 'Generic',
                'modelo' => 'USB-C',
                'precio_compra' => 35,
                'precio_venta' => 99,
                'stock' => 40,
                'stock_minimo' => 10,
                'tipo' => 'accesorio',
            ],
            [
                'codigo' => 'ACC-AUD-BT-001',
                'nombre' => 'Audífonos Bluetooth',
                'descripcion' => 'Audífonos inalámbricos Bluetooth 5.0',
                'categoria_id' => $accesorios->id,
                'marca' => 'Generic',
                'modelo' => 'TWS',
                'precio_compra' => 120,
                'precio_venta' => 299,
                'stock' => 25,
                'stock_minimo' => 5,
                'tipo' => 'accesorio',
            ],
        ];

        // Repuestos
        $productosRepuestos = [
            [
                'codigo' => 'REP-PAN-SAM-S23-001',
                'nombre' => 'Pantalla Samsung S23 Original',
                'descripcion' => 'Pantalla completa original Samsung Galaxy S23',
                'categoria_id' => $repuestos->id,
                'marca' => 'Samsung',
                'modelo' => 'Galaxy S23',
                'precio_compra' => 3500,
                'precio_venta' => 5500,
                'stock' => 3,
                'stock_minimo' => 1,
                'tipo' => 'repuesto',
            ],
            [
                'codigo' => 'REP-PAN-IPH14-001',
                'nombre' => 'Pantalla iPhone 14 Original',
                'descripcion' => 'Pantalla completa original iPhone 14',
                'categoria_id' => $repuestos->id,
                'marca' => 'Apple',
                'modelo' => 'iPhone 14',
                'precio_compra' => 4500,
                'precio_venta' => 6999,
                'stock' => 2,
                'stock_minimo' => 1,
                'tipo' => 'repuesto',
            ],
            [
                'codigo' => 'REP-BAT-SAM-001',
                'nombre' => 'Batería Samsung Original',
                'descripcion' => 'Batería original Samsung varios modelos',
                'categoria_id' => $repuestos->id,
                'marca' => 'Samsung',
                'modelo' => 'Universal',
                'precio_compra' => 200,
                'precio_venta' => 450,
                'stock' => 15,
                'stock_minimo' => 5,
                'tipo' => 'repuesto',
            ],
            [
                'codigo' => 'REP-CEN-IPH-001',
                'nombre' => 'Centro de Carga iPhone',
                'descripcion' => 'Centro de carga flex para iPhone varios modelos',
                'categoria_id' => $repuestos->id,
                'marca' => 'Apple',
                'modelo' => 'Universal',
                'precio_compra' => 80,
                'precio_venta' => 199,
                'stock' => 10,
                'stock_minimo' => 3,
                'tipo' => 'repuesto',
            ],
        ];

        // Servicios
        $productosServicios = [
            [
                'codigo' => 'SRV-DIAG-001',
                'nombre' => 'Diagnóstico',
                'descripcion' => 'Diagnóstico completo del dispositivo',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 200,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-CAM-PAN-001',
                'nombre' => 'Cambio de Pantalla',
                'descripcion' => 'Servicio de cambio de pantalla (no incluye pieza)',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 500,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-CAM-BAT-001',
                'nombre' => 'Cambio de Batería',
                'descripcion' => 'Servicio de cambio de batería (no incluye pieza)',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 300,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-LIM-001',
                'nombre' => 'Limpieza',
                'descripcion' => 'Limpieza interna y externa del dispositivo',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 250,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-SOF-001',
                'nombre' => 'Reinstalación de Software',
                'descripcion' => 'Formateo e instalación de sistema operativo',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 350,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-DESB-001',
                'nombre' => 'Desbloqueo',
                'descripcion' => 'Desbloqueo de cuenta o patrón',
                'categoria_id' => $servicios->id,
                'precio_compra' => 0,
                'precio_venta' => 400,
                'stock' => 9999,
                'stock_minimo' => 0,
                'tipo' => 'servicio',
                'es_servicio' => true,
            ],
        ];

        // Crear todos los productos
        foreach (array_merge($productosCelulares, $productosAccesorios, $productosRepuestos, $productosServicios) as $producto) {
            Producto::create($producto);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuraciones = [
            // General
            ['grupo' => 'general', 'clave' => 'moneda', 'valor' => 'MXN', 'tipo' => 'string', 'descripcion' => 'Moneda principal del sistema'],
            ['grupo' => 'general', 'clave' => 'idioma', 'valor' => 'es', 'tipo' => 'string', 'descripcion' => 'Idioma del sistema'],
            ['grupo' => 'general', 'clave' => 'zona_horaria', 'valor' => 'America/Mexico_City', 'tipo' => 'string', 'descripcion' => 'Zona horaria'],
            
            // Empresa
            ['grupo' => 'empresa', 'clave' => 'nombre', 'valor' => 'CellFix Pro', 'tipo' => 'string', 'descripcion' => 'Nombre de la empresa'],
            ['grupo' => 'empresa', 'clave' => 'direccion', 'valor' => 'Av. Principal #123, Ciudad de México', 'tipo' => 'string', 'descripcion' => 'Dirección de la empresa'],
            ['grupo' => 'empresa', 'clave' => 'telefono', 'valor' => '555-123-4567', 'tipo' => 'string', 'descripcion' => 'Teléfono de contacto'],
            ['grupo' => 'empresa', 'clave' => 'email', 'valor' => 'contacto@cellfix.com', 'tipo' => 'string', 'descripcion' => 'Correo electrónico'],
            ['grupo' => 'empresa', 'clave' => 'rfc', 'valor' => 'ABC123456XYZ', 'tipo' => 'string', 'descripcion' => 'RFC de la empresa'],
            
            // Ventas
            ['grupo' => 'ventas', 'clave' => 'iva_porcentaje', 'valor' => '16', 'tipo' => 'integer', 'descripcion' => 'Porcentaje de IVA'],
            ['grupo' => 'ventas', 'clave' => 'permitir_credito', 'valor' => 'true', 'tipo' => 'boolean', 'descripcion' => 'Permitir ventas a crédito'],
            ['grupo' => 'ventas', 'clave' => 'descuento_maximo', 'valor' => '20', 'tipo' => 'integer', 'descripcion' => 'Descuento máximo permitido (%)'],
            
            // Reparaciones
            ['grupo' => 'reparaciones', 'clave' => 'garantia_default', 'valor' => '30', 'tipo' => 'integer', 'descripcion' => 'Días de garantía por defecto'],
            ['grupo' => 'reparaciones', 'clave' => 'notificar_whatsapp', 'valor' => 'false', 'tipo' => 'boolean', 'descripcion' => 'Notificar vía WhatsApp'],
            ['grupo' => 'reparaciones', 'clave' => 'dias_estimados_default', 'valor' => '3', 'tipo' => 'integer', 'descripcion' => 'Días estimados de reparación'],
            
            // Inventario
            ['grupo' => 'inventario', 'clave' => 'stock_minimo_default', 'valor' => '5', 'tipo' => 'integer', 'descripcion' => 'Stock mínimo por defecto'],
            ['grupo' => 'inventario', 'clave' => 'alertar_stock_bajo', 'valor' => 'true', 'tipo' => 'boolean', 'descripcion' => 'Mostrar alertas de stock bajo'],
            
            // Impresión
            ['grupo' => 'impresion', 'clave' => 'ticket_ancho', 'valor' => '80', 'tipo' => 'integer', 'descripcion' => 'Ancho del ticket en mm'],
            ['grupo' => 'impresion', 'clave' => 'mostrar_logo', 'valor' => 'true', 'tipo' => 'boolean', 'descripcion' => 'Mostrar logo en tickets'],
        ];

        foreach ($configuraciones as $config) {
            Configuracion::create($config);
        }
    }
}

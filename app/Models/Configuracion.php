<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'grupo',
        'clave',
        'valor',
        'tipo',
        'descripcion',
    ];

    const GRUPOS = [
        'general' => 'General',
        'empresa' => 'Empresa',
        'ventas' => 'Ventas',
        'reparaciones' => 'Reparaciones',
        'inventario' => 'Inventario',
        'notificaciones' => 'Notificaciones',
        'impresion' => 'Impresion',
        'facturacion' => 'Facturacion',
    ];

    const DEFAULTS = [
        'general.moneda' => 'MXN',
        'general.idioma' => 'es',
        'general.zona_horaria' => 'America/Mexico_City',

        'empresa.nombre' => 'CellFix Pro',
        'empresa.direccion' => '',
        'empresa.telefono' => '',
        'empresa.email' => '',
        'empresa.rfc' => '',
        'empresa.logo' => '',

        'ventas.iva_porcentaje' => '16',
        'ventas.permitir_credito' => 'true',
        'ventas.descuento_maximo' => '20',
        'ventas.folio_inicial' => '1',

        'reparaciones.garantia_default' => '30',
        'reparaciones.notificar_whatsapp' => 'false',
        'reparaciones.dias_estimados_default' => '3',

        'inventario.stock_minimo_default' => '5',
        'inventario.alertar_stock_bajo' => 'true',

        'notificaciones.email_activo' => 'false',
        'notificaciones.whatsapp_activo' => 'false',

        'impresion.ticket_ancho' => '80',
        'impresion.mostrar_logo' => 'true',

        'facturacion.activo' => 'false',
        'facturacion.cfdi_version' => '4.0',
        'facturacion.serie' => 'A',
        'facturacion.lugar_expedicion' => '',
        'facturacion.regimen_fiscal_emisor' => '',
        'facturacion.exportacion' => '01',
        'facturacion.pac_nombre' => '',
        'facturacion.pac_modo' => 'sandbox',
        'facturacion.pac_url' => '',
        'facturacion.pac_usuario' => '',
        'facturacion.pac_password' => '',
        'facturacion.pac_token' => '',
        'facturacion.certificado_cer' => '',
        'facturacion.certificado_key' => '',
        'facturacion.certificado_password' => '',
        'facturacion.clave_prod_serv_default' => '01010101',
        'facturacion.clave_unidad_default' => 'H87',
        'facturacion.unidad_default' => 'Pieza',
        'facturacion.objeto_impuesto_default' => '02',
    ];

    public function scopeGrupo($query, $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    public static function get(string $clave, $default = null)
    {
        $cacheKey = 'config_' . str_replace('.', '_', $clave);

        return Cache::remember($cacheKey, 3600, function () use ($clave, $default) {
            $parts = explode('.', $clave);
            if (count($parts) !== 2) {
                return $default;
            }

            [$grupo, $key] = $parts;

            $config = self::where('grupo', $grupo)
                ->where('clave', $key)
                ->first();

            if (!$config) {
                return $default ?? self::DEFAULTS[$clave] ?? null;
            }

            return self::castValue($config->valor, $config->tipo);
        });
    }

    public static function set(string $clave, $valor, string $tipo = null): self
    {
        $parts = explode('.', $clave);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('La clave debe tener formato grupo.clave');
        }

        [$grupo, $key] = $parts;

        $tipo = $tipo ?? self::detectType($valor);

        $config = self::updateOrCreate(
            ['grupo' => $grupo, 'clave' => $key],
            ['valor' => self::stringifyValue($valor, $tipo), 'tipo' => $tipo]
        );

        Cache::forget('config_' . str_replace('.', '_', $clave));

        return $config;
    }

    public static function getGrupo(string $grupo): array
    {
        $configs = self::where('grupo', $grupo)->get();
        $result = [];

        foreach ($configs as $config) {
            $result[$config->clave] = self::castValue($config->valor, $config->tipo);
        }

        return $result;
    }

    protected static function castValue(?string $valor, string $tipo): mixed
    {
        if ($valor === null) {
            return null;
        }

        return match ($tipo) {
            'integer' => (int) $valor,
            'boolean' => filter_var($valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($valor, true),
            'float' => (float) $valor,
            default => $valor,
        };
    }

    protected static function detectType($valor): string
    {
        return match (true) {
            is_bool($valor) => 'boolean',
            is_int($valor) => 'integer',
            is_float($valor) => 'float',
            is_array($valor) => 'json',
            default => 'string',
        };
    }

    protected static function stringifyValue($valor, string $tipo): string
    {
        return match ($tipo) {
            'boolean' => $valor ? 'true' : 'false',
            'json' => json_encode($valor),
            default => (string) $valor,
        };
    }

    public static function clearCache(): void
    {
        Cache::flush();
    }
}

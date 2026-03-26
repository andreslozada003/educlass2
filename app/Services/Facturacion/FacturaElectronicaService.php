<?php

namespace App\Services\Facturacion;

use App\Models\Configuracion;
use App\Models\FacturaElectronica;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;

class FacturaElectronicaService
{
    public function obtenerConfiguracion(): array
    {
        return [
            'empresa' => [
                'nombre' => Configuracion::get('empresa.nombre', 'CellFix Pro'),
                'direccion' => Configuracion::get('empresa.direccion', ''),
                'telefono' => Configuracion::get('empresa.telefono', ''),
                'email' => Configuracion::get('empresa.email', ''),
                'rfc' => Configuracion::get('empresa.rfc', ''),
            ],
            'facturacion' => [
                'activo' => (bool) Configuracion::get('facturacion.activo', false),
                'cfdi_version' => Configuracion::get('facturacion.cfdi_version', '4.0'),
                'serie' => Configuracion::get('facturacion.serie', 'A'),
                'lugar_expedicion' => Configuracion::get('facturacion.lugar_expedicion', ''),
                'regimen_fiscal_emisor' => Configuracion::get('facturacion.regimen_fiscal_emisor', ''),
                'exportacion' => Configuracion::get('facturacion.exportacion', '01'),
                'pac_nombre' => Configuracion::get('facturacion.pac_nombre', ''),
                'pac_modo' => Configuracion::get('facturacion.pac_modo', 'sandbox'),
                'pac_url' => Configuracion::get('facturacion.pac_url', ''),
                'pac_usuario' => Configuracion::get('facturacion.pac_usuario', ''),
                'pac_password' => Configuracion::get('facturacion.pac_password', ''),
                'pac_token' => Configuracion::get('facturacion.pac_token', ''),
                'certificado_cer' => Configuracion::get('facturacion.certificado_cer', ''),
                'certificado_key' => Configuracion::get('facturacion.certificado_key', ''),
                'certificado_password' => Configuracion::get('facturacion.certificado_password', ''),
            ],
            'defaults_producto' => [
                'clave_prod_serv_sat' => Configuracion::get('facturacion.clave_prod_serv_default', '01010101'),
                'clave_unidad_sat' => Configuracion::get('facturacion.clave_unidad_default', 'H87'),
                'unidad_sat' => Configuracion::get('facturacion.unidad_default', 'Pieza'),
                'objeto_impuesto' => Configuracion::get('facturacion.objeto_impuesto_default', '02'),
            ],
            'impuestos' => [
                'iva_porcentaje' => (float) Configuracion::get('ventas.iva_porcentaje', 16),
            ],
            'general' => [
                'moneda' => Configuracion::get('general.moneda', 'MXN'),
            ],
        ];
    }

    public function revisarVenta(Venta $venta): array
    {
        $venta->loadMissing(['cliente', 'detalles.producto']);

        $config = $this->obtenerConfiguracion();
        $errores = [];
        $advertencias = [];

        if ($venta->estado === 'cancelada') {
            $errores[] = 'La venta esta cancelada y no puede facturarse.';
        }

        if (!$venta->cliente) {
            $errores[] = 'La venta no tiene un cliente asociado.';
        }

        if ($venta->detalles->isEmpty()) {
            $errores[] = 'La venta no tiene conceptos para facturar.';
        }

        if (blank($config['empresa']['nombre'])) {
            $errores[] = 'Falta el nombre del emisor en la configuracion de facturacion.';
        }

        if (blank($config['empresa']['rfc'])) {
            $errores[] = 'Falta el RFC del emisor.';
        }

        if (blank($config['facturacion']['lugar_expedicion'])) {
            $errores[] = 'Falta el lugar de expedicion.';
        }

        if (blank($config['facturacion']['regimen_fiscal_emisor'])) {
            $errores[] = 'Falta el regimen fiscal del emisor.';
        }

        if ($venta->cliente) {
            if (blank($venta->cliente->rfc)) {
                $errores[] = 'El cliente no tiene RFC.';
            }
            if (blank($venta->cliente->nombre_fiscal)) {
                $errores[] = 'El cliente no tiene nombre o razon social para CFDI.';
            }
            if (blank($venta->cliente->codigo_postal)) {
                $errores[] = 'El cliente no tiene codigo postal fiscal.';
            }
            if (blank($venta->cliente->regimen_fiscal)) {
                $errores[] = 'El cliente no tiene regimen fiscal.';
            }
            if (blank($venta->cliente->uso_cfdi)) {
                $errores[] = 'El cliente no tiene uso CFDI predeterminado.';
            }
        }

        foreach ($venta->detalles as $detalle) {
            if (!$detalle->producto) {
                $errores[] = 'Uno de los productos de la venta ya no existe.';
                continue;
            }

            if ($detalle->producto->clave_prod_serv_sat === '01010101') {
                $advertencias[] = "El producto {$detalle->producto->nombre} usa la ClaveProdServ generica 01010101.";
            }

            if ($detalle->producto->clave_unidad_sat === 'H87') {
                $advertencias[] = "El producto {$detalle->producto->nombre} usa la clave de unidad generica H87.";
            }
        }

        if (blank($config['facturacion']['pac_nombre'])) {
            $advertencias[] = 'Aun no se ha configurado un PAC. La factura se guardara solo como borrador listo para conectar.';
        }

        return [
            'errores' => array_values(array_unique($errores)),
            'advertencias' => array_values(array_unique($advertencias)),
        ];
    }

    public function preparar(Venta $venta, ?int $userId = null): FacturaElectronica
    {
        $revision = $this->revisarVenta($venta);

        if (!empty($revision['errores'])) {
            throw ValidationException::withMessages([
                'facturacion' => $revision['errores'],
            ]);
        }

        $config = $this->obtenerConfiguracion();
        $venta->loadMissing(['cliente', 'detalles.producto']);

        $ivaPorcentaje = $config['impuestos']['iva_porcentaje'];
        $ivaFactor = round($ivaPorcentaje / 100, 6);

        $emisor = [
            'nombre' => $config['empresa']['nombre'],
            'rfc' => $config['empresa']['rfc'],
            'direccion' => $config['empresa']['direccion'],
            'telefono' => $config['empresa']['telefono'],
            'email' => $config['empresa']['email'],
            'lugar_expedicion' => $config['facturacion']['lugar_expedicion'],
            'regimen_fiscal' => $config['facturacion']['regimen_fiscal_emisor'],
        ];

        $receptor = [
            'nombre' => $venta->cliente->nombre_fiscal,
            'rfc' => $venta->cliente->rfc,
            'codigo_postal' => $venta->cliente->codigo_postal,
            'regimen_fiscal' => $venta->cliente->regimen_fiscal,
            'uso_cfdi' => $venta->cliente->uso_cfdi,
            'email' => $venta->cliente->email,
        ];

        $conceptos = $venta->detalles->map(function ($detalle) use ($config, $ivaFactor) {
            $producto = $detalle->producto;
            $objetoImpuesto = $producto->objeto_impuesto ?: $config['defaults_producto']['objeto_impuesto'];
            $importeImpuesto = $objetoImpuesto === '02'
                ? round((float) $detalle->subtotal * $ivaFactor, 2)
                : 0;

            return [
                'producto_id' => $producto->id,
                'codigo' => $producto->codigo,
                'descripcion' => $producto->nombre,
                'cantidad' => (int) $detalle->cantidad,
                'valor_unitario' => round((float) $detalle->precio_unitario, 2),
                'importe' => round((float) $detalle->subtotal, 2),
                'clave_prod_serv' => $producto->clave_prod_serv_sat ?: $config['defaults_producto']['clave_prod_serv_sat'],
                'clave_unidad' => $producto->clave_unidad_sat ?: $config['defaults_producto']['clave_unidad_sat'],
                'unidad' => $producto->unidad_sat ?: $config['defaults_producto']['unidad_sat'],
                'objeto_impuesto' => $objetoImpuesto,
                'impuestos' => $objetoImpuesto === '02'
                    ? [
                        'traslados' => [
                            [
                                'impuesto' => '002',
                                'tipo_factor' => 'Tasa',
                                'tasa_o_cuota' => $ivaFactor,
                                'importe' => $importeImpuesto,
                            ],
                        ],
                    ]
                    : [],
            ];
        })->values()->all();

        $payload = [
            'comprobante' => [
                'version' => $config['facturacion']['cfdi_version'],
                'serie' => $config['facturacion']['serie'],
                'folio_referencia' => $venta->folio,
                'fecha' => $venta->fecha_venta?->format('Y-m-d\TH:i:s'),
                'moneda' => $config['general']['moneda'],
                'tipo_comprobante' => 'I',
                'forma_pago' => $this->resolverFormaPago($venta->metodo_pago),
                'metodo_pago' => $this->resolverMetodoPago($venta->metodo_pago),
                'exportacion' => $config['facturacion']['exportacion'],
                'subtotal' => round((float) $venta->subtotal, 2),
                'descuento' => round((float) $venta->descuento, 2),
                'impuestos' => round((float) $venta->impuestos, 2),
                'total' => round((float) $venta->total, 2),
                'lugar_expedicion' => $config['facturacion']['lugar_expedicion'],
            ],
            'emisor' => $emisor,
            'receptor' => $receptor,
            'conceptos' => $conceptos,
        ];

        return FacturaElectronica::updateOrCreate(
            ['venta_id' => $venta->id],
            [
                'cliente_id' => $venta->cliente_id,
                'user_id' => $userId,
                'estado' => 'lista_para_timbrar',
                'cfdi_version' => $config['facturacion']['cfdi_version'],
                'tipo_comprobante' => 'I',
                'serie' => $config['facturacion']['serie'],
                'folio' => (string) $venta->id,
                'moneda' => $config['general']['moneda'],
                'forma_pago' => $this->resolverFormaPago($venta->metodo_pago),
                'metodo_pago_sat' => $this->resolverMetodoPago($venta->metodo_pago),
                'uso_cfdi' => $venta->cliente->uso_cfdi,
                'exportacion' => $config['facturacion']['exportacion'],
                'lugar_expedicion' => $config['facturacion']['lugar_expedicion'],
                'regimen_fiscal_emisor' => $config['facturacion']['regimen_fiscal_emisor'],
                'regimen_fiscal_receptor' => $venta->cliente->regimen_fiscal,
                'subtotal' => $venta->subtotal,
                'descuento' => $venta->descuento,
                'impuestos' => $venta->impuestos,
                'total' => $venta->total,
                'pac_driver' => $config['facturacion']['pac_nombre'],
                'pac_modo' => $config['facturacion']['pac_modo'],
                'error_mensaje' => null,
                'emisor_datos' => $emisor,
                'receptor_datos' => $receptor,
                'conceptos' => $conceptos,
                'payload_preparado' => $payload,
            ]
        );
    }

    protected function resolverFormaPago(string $metodoPago): string
    {
        return match ($metodoPago) {
            'efectivo' => '01',
            'deposito' => '02',
            'transferencia' => '03',
            'tarjeta' => '04',
            default => '99',
        };
    }

    protected function resolverMetodoPago(string $metodoPago): string
    {
        return $metodoPago === 'credito' ? 'PPD' : 'PUE';
    }
}

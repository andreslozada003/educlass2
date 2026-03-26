<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FacturaElectronica extends Model
{
    use HasFactory;

    protected $table = 'facturas_electronicas';

    protected $fillable = [
        'folio_interno',
        'venta_id',
        'cliente_id',
        'user_id',
        'estado',
        'cfdi_version',
        'tipo_comprobante',
        'serie',
        'folio',
        'moneda',
        'forma_pago',
        'metodo_pago_sat',
        'uso_cfdi',
        'exportacion',
        'lugar_expedicion',
        'regimen_fiscal_emisor',
        'regimen_fiscal_receptor',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'pac_driver',
        'pac_modo',
        'uuid',
        'xml_path',
        'pdf_path',
        'acuse_cancelacion_path',
        'fecha_timbrado',
        'fecha_cancelacion',
        'intentos_timbrado',
        'error_mensaje',
        'emisor_datos',
        'receptor_datos',
        'conceptos',
        'payload_preparado',
        'respuesta_pac',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_timbrado' => 'datetime',
        'fecha_cancelacion' => 'datetime',
        'emisor_datos' => 'array',
        'receptor_datos' => 'array',
        'conceptos' => 'array',
        'payload_preparado' => 'array',
        'respuesta_pac' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($factura) {
            if (empty($factura->folio_interno)) {
                $factura->folio_interno = 'CFDI-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
            }
        });
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

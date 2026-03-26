<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'apellido',
        'razon_social',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'notas',
        'fecha_nacimiento',
        'rfc',
        'regimen_fiscal',
        'uso_cfdi',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('apellido', 'like', "%{$search}%")
                ->orWhere('razon_social', 'like', "%{$search}%")
                ->orWhere('telefono', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('rfc', 'like', "%{$search}%");
        });
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function getNombreFiscalAttribute(): string
    {
        return $this->razon_social ?: $this->nombre_completo;
    }

    public function getWhatsappLinkAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->telefono);

        return "https://wa.me/{$phone}";
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class);
    }

    public function facturasElectronicas()
    {
        return $this->hasMany(FacturaElectronica::class);
    }

    public function getTotalGastadoAttribute(): float
    {
        return $this->ventas()->sum('total');
    }

    public function getTotalReparacionesAttribute(): int
    {
        return $this->reparaciones()->count();
    }

    public function getUltimaCompraAttribute()
    {
        return $this->ventas()->latest()->first();
    }
}

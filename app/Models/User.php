<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope para usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Verificar si es admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verificar si es técnico
     */
    public function isTecnico(): bool
    {
        return $this->hasRole('tecnico');
    }

    /**
     * Verificar si es vendedor
     */
    public function isVendedor(): bool
    {
        return $this->hasRole('vendedor');
    }

    /**
     * Relación con ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Relación con reparaciones como técnico
     */
    public function reparacionesAsTecnico()
    {
        return $this->hasMany(Reparacion::class, 'tecnico_id');
    }

    /**
     * Relación con notificaciones
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    /**
     * Notificaciones no leídas
     */
    public function notificacionesNoLeidas()
    {
        return $this->hasMany(Notificacion::class)->where('leida', false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MoraNotificacion extends Model
{
    use HasFactory;

    protected $table = 'mora_notificaciones';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'canal',
        'nivel',
        'plantilla',
        'telefono',
        'estado_envio',
        'fecha_envio',
        'mensaje',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];

    public function moraNotificable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

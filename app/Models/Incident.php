<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Incident extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'incident_code',
        'user_id',
        'assigned_to',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'related_type',
        'related_id',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incident) {
            if (empty($incident->incident_code)) {
                $incident->incident_code = self::generateIncidentCode();
            }
        });

        static::updating(function ($incident) {
            if ($incident->isDirty('status') && $incident->status === 'resolved' && empty($incident->resolved_at)) {
                $incident->resolved_at = now();
                $incident->resolved_by = auth()->id();
            }
        });
    }

    /**
     * Generate unique incident code.
     */
    public static function generateIncidentCode(): string
    {
        $prefix = 'INC';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $code = $prefix . '-' . $date . '-' . $random;
        
        while (self::where('incident_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = $prefix . '-' . $date . '-' . $random;
        }
        
        return $code;
    }

    /**
     * Get the user who created this incident.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to this incident.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who resolved this incident.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the related model (polymorphic).
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get open incidents.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope a query to get resolved incidents.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to get incidents assigned to a user.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Check if incident is open.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    /**
     * Check if incident is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Resolve the incident.
     */
    public function resolve(string $notes): void
    {
        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->resolved_by = auth()->id();
        $this->resolution_notes = $notes;
        $this->save();
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'open' => 'Abierta',
            'in_progress' => 'En progreso',
            'resolved' => 'Resuelta',
            'closed' => 'Cerrada',
            'cancelled' => 'Cancelada',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'open' => 'danger',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'cancelled' => 'dark',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        $labels = [
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Crítica',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'inventory' => 'Inventario',
            'sale' => 'Venta',
            'repair' => 'Reparación',
            'customer' => 'Cliente',
            'system' => 'Sistema',
            'other' => 'Otro',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}

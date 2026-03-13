<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Service.
 * Responsabilidad ÚNICA: Representar la estructura de datos de un servicio y sus relaciones.
 * SIN lógica de negocio.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $duration_minutes
 * @property bool $is_active
 */
class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // ==================== RELACIONES ====================

    /**
     * Un servicio tiene muchas citas asignadas.
     *
     * @return HasMany
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // ==================== SCOPES ====================

    /**
     * Filtra servicios activos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtra servicios inactivos.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Filtra servicios por nombre (case-insensitive).
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%']);
    }

    /**
     * Filtra servicios dentro de un rango de duración (en minutos).
     */
    public function scopeByDurationRange(Builder $query, int $minMinutes, int $maxMinutes): Builder
    {
        return $query->whereBetween('duration_minutes', [$minMinutes, $maxMinutes]);
    }

    // ==================== MÉTODOS HELPER ====================

    /**
     * Obtiene la duración en formato legible (ej: "1h 30m").
     */
    public function getDurationFormatted(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }

        return implode(' ', $parts) ?: '0m';
    }
}

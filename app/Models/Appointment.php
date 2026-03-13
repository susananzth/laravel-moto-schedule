<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Appointment.
 * Responsabilidad ÚNICA: Representar la estructura de datos y relaciones de una cita.
 * SIN lógica de negocio.
 *
 * @property int $id
 * @property int $user_id
 * @property int $service_id
 * @property int|null $technician_id
 * @property Carbon $scheduled_at
 * @property Carbon|null $finished_at
 * @property string $status
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'technician_id',
        'service_id',
        'scheduled_at',
        'finished_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    // ==================== RELACIONES ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // ==================== SCOPES ====================

    /**
     * Filtra citas dentro de un rango de fechas.
     */
    public function scopeInDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('scheduled_at', [$startDate, $endDate]);
    }

    /**
     * Filtra citas por estado.
     */
    public function scopeByStatus(Builder $query, AppointmentStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * Filtra citas pendientes de confirmación.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->byStatus(AppointmentStatus::PENDING);
    }

    /**
     * Filtra citas confirmadas.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->byStatus(AppointmentStatus::CONFIRMED);
    }

    /**
     * Filtra citas completadas.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->byStatus(AppointmentStatus::COMPLETED);
    }

    /**
     * Filtra citas canceladas.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->byStatus(AppointmentStatus::CANCELLED);
    }

    /**
     * Filtra citas asignadas a un técnico.
     */
    public function scopeAssignedToTechnician(Builder $query, int $technicianId): Builder
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Filtra citas sin técnico asignado.
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('technician_id');
    }

    /**
     * Filtra citas de un cliente específico.
     */
    public function scopeByClient(Builder $query, int $clientId): Builder
    {
        return $query->where('user_id', $clientId);
    }

    // ==================== MÉTODOS HELPER (SIN LÓGICA DE NEGOCIO) ====================

    /**
     * Verifica si la cita está completada o cancelada.
     */
    public function isCompletedOrCancelled(): bool
    {
        return in_array(
            $this->status,
            [AppointmentStatus::COMPLETED->value, AppointmentStatus::CANCELLED->value],
            strict: true
        );
    }

    /**
     * Obtiene el estado como Enum.
     */
    public function getStatusEnum(): AppointmentStatus
    {
        return AppointmentStatus::from($this->status);
    }

    /**
     * Verifica si la cita está asignada.
     */
    public function isAssigned(): bool
    {
        return $this->technician_id !== null;
    }

    /**
     * Verifica si la cita está en el pasado.
     */
    public function isPast(): bool
    {
        return $this->scheduled_at->isPast();
    }

    /**
     * Verifica si la cita está próxima (dentro de las próximas 24 horas).
     */
    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isBetween(
            Carbon::now(),
            Carbon::now()->addDay()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Repositories;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class AppointmentRepository
{
    /**
     * Busca una cita por ID.
     */
    public function findById(int $id): ?Appointment
    {
        return Appointment::with(['client', 'service', 'technician'])
            ->find($id);
    }

    /**
     * Busca una cita por ID o lanza excepción.
     */
    public function findByIdOrFail(int $id): Appointment
    {
        return Appointment::with(['client', 'service', 'technician'])
            ->findOrFail($id);
    }

    /**
     * Obtiene todas las citas para un período específico.
     *
     * @return Collection<int, Appointment>
     */
    public function findByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return Appointment::with(['client', 'service', 'technician'])
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Obtiene citas asignadas a un técnico en un período.
     *
     * @return Collection<int, Appointment>
     */
    public function findByTechnicianAndDateRange(
        User $technician,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return Appointment::with(['client', 'service'])
            ->where('technician_id', $technician->id)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Obtiene citas de un cliente en un período.
     *
     * @return Collection<int, Appointment>
     */
    public function findByClientAndDateRange(
        User $client,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return Appointment::with(['service', 'technician'])
            ->where('user_id', $client->id)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Obtiene citas pendientes de confirmación.
     *
     * @return Collection<int, Appointment>
     */
    public function findPending(): Collection
    {
        return Appointment::with(['client', 'service', 'technician'])
            ->where('status', AppointmentStatus::PENDING->value)
            ->get();
    }

    /**
     * Obtiene citas sin técnico asignado.
     *
     * @return Collection<int, Appointment>
     */
    public function findUnassigned(): Collection
    {
        return Appointment::with(['client', 'service'])
            ->whereNull('technician_id')
            ->where('status', '!=', AppointmentStatus::CANCELLED->value)
            ->get();
    }

    /**
     * Guarda una cita en la base de datos.
     */
    public function save(Appointment $appointment): Appointment
    {
        $appointment->save();
        return $appointment->refresh();
    }

    /**
     * Obtiene estadísticas de citas para un período.
     *
     * @return array<string, int>
     */
    public function getStatsByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $appointments = $this->findByDateRange($startDate, $endDate);

        return [
            'total' => $appointments->count(),
            'pending' => $appointments->where('status', AppointmentStatus::PENDING->value)->count(),
            'confirmed' => $appointments->where('status', AppointmentStatus::CONFIRMED->value)->count(),
            'completed' => $appointments->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'cancelled' => $appointments->where('status', AppointmentStatus::CANCELLED->value)->count(),
        ];
    }
}

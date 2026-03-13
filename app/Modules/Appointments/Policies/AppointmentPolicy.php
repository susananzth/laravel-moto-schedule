<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Policies;

use App\Models\Appointment;
use App\Models\User;

final class AppointmentPolicy
{
    /**
     * Ver todas las citas (solo admin).
     */
    public function viewAll(User $user): bool
    {
        return $user->hasPermissionTo('appointments.view_all');
    }

    /**
     * Ver una cita específica.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Admin ve todo
        if ($this->viewAll($user)) {
            return true;
        }

        // Técnico asignado ve su cita
        if ($user->hasPermissionTo('appointments.be_assigned') && $appointment->technician_id === $user->id) {
            return true;
        }

        // Cliente solo ve sus propias citas
        return $appointment->user_id === $user->id;
    }

    /**
     * Actualizar una cita (estado, técnico, notas).
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // No se puede modificar citas completadas o canceladas
        if ($appointment->isCompletedOrCancelled()) {
            return false;
        }

        // Solo admin puede actualizar citas
        return $user->hasPermissionTo('appointments.assign');
    }

    /**
     * Técnico puede marcar como completada.
     */
    public function complete(User $user, Appointment $appointment): bool
    {
        // Solo el técnico asignado puede marcar como completada
        return $user->hasPermissionTo('appointments.be_assigned')
            && $appointment->technician_id === $user->id
            && $appointment->status !== 'completed';
    }

    /**
     * Cancelar una cita.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        // No se puede cancelar si ya está completada
        if ($appointment->status === 'completed') {
            return false;
        }

        // Admin puede cancelar cualquier cita
        if ($user->hasPermissionTo('appointments.assign')) {
            return true;
        }

        // Una vez confirmada, solo admin puede cancelar
        if ($appointment->status === 'confirmed') {
            return false;
        }

        // Cliente puede cancelar su propia cita si es pendiente
        return $appointment->user_id === $user->id
            && $appointment->status === 'pending';
    }

    /**
     * Reprogramar una cita.
     */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        // No se puede reprogramar completadas o canceladas
        if ($appointment->isCompletedOrCancelled()) {
            return false;
        }

        // Solo admin puede reprogramar
        if ($user->hasPermissionTo('appointments.assign')) {
            return true;
        }

        // Cliente solo sus propias citas
        if ($user->hasPermissionTo('appointments.edit')) {
            return $appointment->user_id === $user->id;
        }

        return false;
    }

    /**
     * Asignar técnico a una cita.
     */
    public function assignTechnician(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('appointments.assign');
    }
}

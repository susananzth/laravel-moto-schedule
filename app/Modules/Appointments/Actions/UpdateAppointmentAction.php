<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Actions;

use App\Core\Enums\AppointmentStatus;
use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Modules\Appointments\DTOs\UpdateAppointmentDTO;
use App\Modules\Appointments\Exceptions\InvalidAppointmentStatusTransitionException;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use Illuminate\Support\Facades\Mail;

final class UpdateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    /**
     * Actualiza una cita con nuevos datos.
     *
     * @throws InvalidAppointmentStatusTransitionException
     */
    public function execute(Appointment $appointment, UpdateAppointmentDTO $dto): Appointment
    {
        $currentStatus = $appointment->getStatusEnum();

        // Validar transición de estado
        if (!$currentStatus->canTransitionTo($dto->status)) {
            throw InvalidAppointmentStatusTransitionException::make(
                $currentStatus->value,
                $dto->status->value
            );
        }

        // Validar que se asigne técnico antes de confirmar (excepto si ya estaba asignado)
        if ($dto->status === AppointmentStatus::CONFIRMED && !$dto->technicianId && !$appointment->isAssigned()) {
            throw new \DomainException(
                'Debe asignar un técnico antes de confirmar la cita.'
            );
        }

        // Actualizar cita
        $appointment->update([
            'status' => $dto->status->value,
            'technician_id' => $dto->technicianId ?? $appointment->technician_id,
            'notes' => $dto->notes ?? $appointment->notes,
        ]);

        $appointment = $this->appointmentRepository->save($appointment);

        // Notificar cambios importantes
        $this->notifyIfNecessary($appointment, $currentStatus);

        return $appointment;
    }

    /**
     * Envía notificaciones según el cambio de estado.
     */
    private function notifyIfNecessary(Appointment $appointment, AppointmentStatus $previousStatus): void
    {
        $currentStatus = $appointment->getStatusEnum();

        // Notificar al cliente si se confirma
        if ($currentStatus === AppointmentStatus::CONFIRMED && $previousStatus === AppointmentStatus::PENDING) {
            Mail::to($appointment->client->email)
                ->send(new AppointmentNotification($appointment, 'confirmed'));
        }

        // Notificar si se cancela
        if ($currentStatus === AppointmentStatus::CANCELLED) {
            Mail::to($appointment->client->email)
                ->send(new AppointmentNotification($appointment, 'cancelled'));
        }
    }
}

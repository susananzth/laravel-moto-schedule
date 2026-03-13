<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Actions;

use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Modules\Appointments\DTOs\RescheduleAppointmentDTO;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use App\Modules\Appointments\ValueObjects\AppointmentDateTime;
use Illuminate\Support\Facades\Mail;

final class RescheduleAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    /**
     * Reprograma una cita a una nueva fecha/hora.
     *
     * @throws \App\Modules\Appointments\Exceptions\InvalidAppointmentDateException
     */
    public function execute(Appointment $appointment, RescheduleAppointmentDTO $dto): Appointment
    {
        // Validar que la nueva fecha sea válida (usando Value Object)
        $validDateTime = AppointmentDateTime::createForReschedule(
            $dto->newScheduledAt,
            $appointment->scheduled_at
        );

        // Actualizar cita
        $appointment->update([
            'scheduled_at' => $validDateTime->dateTime,
        ]);

        $appointment = $this->appointmentRepository->save($appointment);

        // Notificar al cliente
        Mail::to($appointment->client->email)
            ->send(new AppointmentNotification($appointment, 'rescheduled'));

        // Notificar al técnico si está asignado
        if ($appointment->isAssigned()) {
            Mail::to($appointment->technician->email)
                ->send(new AppointmentNotification($appointment, 'rescheduled'));
        }

        return $appointment;
    }
}

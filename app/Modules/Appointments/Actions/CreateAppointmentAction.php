<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Actions;

use App\Core\Enums\AppointmentStatus;
use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Modules\Appointments\DTOs\CreateAppointmentDTO;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use App\Modules\Appointments\ValueObjects\AppointmentDateTime;
use Illuminate\Support\Facades\Mail;

final class CreateAppointmentAction
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    /**
     * Crea una nueva cita.
     *
     * @throws \App\Modules\Appointments\Exceptions\InvalidAppointmentDateException
     */
    public function execute(CreateAppointmentDTO $dto): Appointment
    {
        // Validar que la fecha sea válida
        $validDateTime = AppointmentDateTime::create($dto->scheduledAt);

        // Crear la cita
        $appointment = new Appointment([
            'user_id' => $dto->userId,
            'service_id' => $dto->serviceId,
            'technician_id' => $dto->technicianId,
            'scheduled_at' => $validDateTime->dateTime,
            'status' => AppointmentStatus::PENDING->value,
            'notes' => $dto->notes,
        ]);

        $appointment = $this->appointmentRepository->save($appointment);

        // Notificar al cliente
        $client = User::findOrFail($dto->userId);
        Mail::to($client->email)
            ->send(new AppointmentNotification($appointment, 'created'));

        // Notificar al técnico si está asignado
        if ($appointment->isAssigned()) {
            $technician = User::findOrFail($appointment->technician_id);
            Mail::to($technician->email)
                ->send(new AppointmentNotification($appointment, 'assigned'));
        }

        return $appointment;
    }
}

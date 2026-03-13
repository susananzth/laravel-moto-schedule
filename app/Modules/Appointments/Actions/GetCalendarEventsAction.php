<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Actions;

use App\Models\User;
use App\Modules\Appointments\DTOs\CalendarEventDTO;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class GetCalendarEventsAction
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
    ) {
    }

    /**
     * Obtiene los eventos del calendario para un usuario en un período específico.
     *
     * @return SupportCollection<int, CalendarEventDTO>
     */
    public function execute(User $user, Carbon $startDate, Carbon $endDate): SupportCollection
    {
        // Obtener citas según permisos del usuario
        $appointments = match (true) {
            $user->hasPermissionTo('appointments.view_all') =>
                $this->appointmentRepository->findByDateRange($startDate, $endDate),
            $user->hasPermissionTo('appointments.be_assigned') =>
                $this->appointmentRepository->findByTechnicianAndDateRange($user, $startDate, $endDate),
            default =>
                $this->appointmentRepository->findByClientAndDateRange($user, $startDate, $endDate),
        };

        // Transformar a DTOs
        return $appointments->map(
            fn ($appointment) => $this->mapToCalendarEventDTO($appointment, $user)
        );
    }

    /**
     * Transforma una cita en un evento de calendario.
     */
    private function mapToCalendarEventDTO(
        \App\Models\Appointment $appointment,
        User $user
    ): CalendarEventDTO {
        $status = $appointment->getStatusEnum();

        // Título según el rol del usuario
        $title = $user->hasRole('Cliente')
            ? $appointment->service->name
            : $appointment->client->firstname . ' - ' . $appointment->service->name;

        return new CalendarEventDTO(
            id: $appointment->id,
            title: $title,
            start: $appointment->scheduled_at,
            color: $status->color(),
            extendedProps: [
                'status' => $appointment->status,
                'statusLabel' => $status->label(),
                'technician' => $appointment->technician?->firstname . ' ' . $appointment->technician?->lastname ?? 'Sin asignar',
                'notes' => $appointment->notes,
            ],
        );
    }
}

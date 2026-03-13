<?php

declare(strict_types=1);

namespace App\Modules\Appointments\DTOs;

use Carbon\Carbon;

final class RescheduleAppointmentDTO
{
    public function __construct(
        public readonly int $appointmentId,
        public readonly Carbon $newScheduledAt,
    ) {
    }
}

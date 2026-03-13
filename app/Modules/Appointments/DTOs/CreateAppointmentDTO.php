<?php

declare(strict_types=1);

namespace App\Modules\Appointments\DTOs;

use Carbon\Carbon;

final class CreateAppointmentDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $serviceId,
        public readonly Carbon $scheduledAt,
        public readonly ?int $technicianId = null,
        public readonly string $notes = '',
    ) {
    }
}

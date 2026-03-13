<?php

declare(strict_types=1);

namespace App\Modules\Appointments\DTOs;

use App\Core\Enums\AppointmentStatus;

final class UpdateAppointmentDTO
{
    public function __construct(
        public readonly AppointmentStatus $status,
        public readonly ?int $technicianId = null,
        public readonly ?string $notes = null,
    ) {
    }
}

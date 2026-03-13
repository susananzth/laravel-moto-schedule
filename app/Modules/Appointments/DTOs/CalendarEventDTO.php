<?php

declare(strict_types=1);

namespace App\Modules\Appointments\DTOs;

use Carbon\Carbon;

final class CalendarEventDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly Carbon $start,
        public readonly string $color,
        public readonly array $extendedProps,
    ) {
    }

    /**
     * Convierte el DTO a un array para la vista.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start->toIso8601String(),
            'color' => $this->color,
            'extendedProps' => $this->extendedProps,
        ];
    }
}

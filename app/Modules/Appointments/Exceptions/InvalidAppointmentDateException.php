<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Exceptions;

use Exception;

final class InvalidAppointmentDateException extends Exception
{
    public static function pastDate(): self
    {
        return new self('No se puede programar una cita en el pasado.');
    }

    public static function sameAsCurrentDate(): self
    {
        return new self('La nueva fecha y hora son iguales a la actual.');
    }

    public static function insufficientNotice(int $hours): self
    {
        return new self(
            "La cita debe programarse con al menos {$hours} hora(s) de antelación."
        );
    }

    public static function outsideBusinessHours(): self
    {
        return new self('La cita debe programarse en horario laboral (Lunes a Viernes, 08:00 - 18:00).');
    }
}

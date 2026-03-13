<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Exceptions;

use Exception;

final class InvalidAppointmentStatusTransitionException extends Exception
{
    public static function make(string $from, string $to): self
    {
        return new self(
            "Transición inválida de estado '{$from}' a '{$to}'."
        );
    }
}

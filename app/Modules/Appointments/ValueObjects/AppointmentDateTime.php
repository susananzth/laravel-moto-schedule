<?php

declare(strict_types=1);

namespace App\Modules\Appointments\ValueObjects;

use App\Modules\Appointments\Exceptions\InvalidAppointmentDateException;
use Carbon\Carbon;

final class AppointmentDateTime
{
    private const BUSINESS_HOURS_START = 8;
    private const BUSINESS_HOURS_END = 18;
    private const MIN_ADVANCE_NOTICE_HOURS = 1;
    private const MIN_RESCHEDULE_NOTICE_HOURS = 24;

    public function __construct(
        public readonly Carbon $dateTime,
        private readonly bool $isReschedule = false,
    ) {
        $this->validate();
    }

    /**
     * Crea una instancia para crear una nueva cita (validación estándar).
     */
    public static function create(Carbon $dateTime): self
    {
        return new self($dateTime, isReschedule: false);
    }

    /**
     * Crea una instancia para reprogramar una cita existente.
     * Requiere validación adicional: fecha diferente y mayor antelación.
     */
    public static function createForReschedule(
        Carbon $dateTime,
        Carbon $currentScheduledAt
    ): self {
        // Validar que sea diferente ANTES de crear la instancia
        if ($dateTime->eq($currentScheduledAt)) {
            throw InvalidAppointmentDateException::sameAsCurrentDate();
        }

        // Crear instancia con flag de reschedule (usa validación más estricta)
        return new self($dateTime, isReschedule: true);
    }

    /**
     * Valida que la fecha sea válida según el tipo de operación.
     */
    private function validate(): void
    {
        if ($this->dateTime->isPast()) {
            throw InvalidAppointmentDateException::pastDate();
        }

        if (!$this->isBusinessHours()) {
            throw InvalidAppointmentDateException::outsideBusinessHours();
        }

        $minimumHours = $this->isReschedule
            ? self::MIN_RESCHEDULE_NOTICE_HOURS
            : self::MIN_ADVANCE_NOTICE_HOURS;

        if (!$this->hasMinimumAdvanceNotice($minimumHours)) {
            throw InvalidAppointmentDateException::insufficientNotice($minimumHours);
        }
    }

    /**
     * Valida que esté dentro del horario laboral.
     */
    private function isBusinessHours(): bool
    {
        $isWeekday = $this->dateTime->dayOfWeek >= 1 && $this->dateTime->dayOfWeek <= 5;
        $isBusinessHour = $this->dateTime->hour >= self::BUSINESS_HOURS_START
            && $this->dateTime->hour < self::BUSINESS_HOURS_END;

        return $isWeekday && $isBusinessHour;
    }

    /**
     * Valida que tenga el aviso mínimo de antelación.
     */
    private function hasMinimumAdvanceNotice(int $minimumHours): bool
    {
        return Carbon::now()->diffInHours($this->dateTime) >= $minimumHours;
    }

    /**
     * Valida que la fecha sea diferente a la actual.
     */
    public function isDifferentFrom(Carbon $other): bool
    {
        return !$this->dateTime->eq($other);
    }

    /**
     * Obtiene la fecha y hora como Carbon.
     */
    public function toCarbon(): Carbon
    {
        return $this->dateTime;
    }
}

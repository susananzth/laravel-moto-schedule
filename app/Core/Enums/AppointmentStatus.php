<?php

declare(strict_types=1);

namespace App\Core\Enums;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::CONFIRMED => 'Confirmada',
            self::IN_PROGRESS => 'En Progreso',
            self::COMPLETED => 'Completada',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#F59E0B',      // Ambar
            self::CONFIRMED => '#3B82F6',    // Azul
            self::IN_PROGRESS => '#8B5CF6',  // Púrpura
            self::COMPLETED => '#10B981',    // Verde
            self::CANCELLED => '#EF4444',    // Rojo
        };
    }

    /**
     * Obtiene las transiciones permitidas desde este estado.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::IN_PROGRESS, self::CANCELLED],
            self::IN_PROGRESS => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        // Si el estado es el mismo, se permite (para cambiar otros datos)
        if ($this === $target) {
            return true;
        }

        return in_array($target, $this->allowedTransitions(), strict: true);
    }
}

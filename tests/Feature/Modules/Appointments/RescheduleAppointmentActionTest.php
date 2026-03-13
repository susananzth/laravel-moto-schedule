<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Modules\Appointments\Actions\RescheduleAppointmentAction;
use App\Modules\Appointments\DTOs\RescheduleAppointmentDTO;
use App\Modules\Appointments\Exceptions\InvalidAppointmentDateException;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RescheduleAppointmentActionTest extends TestCase
{
    use RefreshDatabase;

    private RescheduleAppointmentAction $action;
    private AppointmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->repository = $this->app->make(AppointmentRepository::class);
        $this->action = $this->app->make(RescheduleAppointmentAction::class);
    }

    // ==================== TEST: Reprogramación Válida ====================

    public function test_can_reschedule_to_valid_business_hours(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $this->nextBusinessDay(3)]);

        $newDateTime = $this->nextBusinessDay(5)->setHour(14)->setMinute(0)->setSecond(0);
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        // Comparar sin milisegundos debido a conversiones de BD
        $this->assertTrue(
            $result->scheduled_at->format('Y-m-d H:i') === $newDateTime->format('Y-m-d H:i')
        );
    }

    // ==================== TEST: Validaciones de Fecha ====================

    public function test_cannot_reschedule_to_past_date(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $this->nextBusinessDay(7)]);

        $pastDateTime = Carbon::now()->subDays(1);
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $pastDateTime,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->expectExceptionMessage('No se puede programar una cita en el pasado');
        $this->action->execute($appointment, $dto);
    }

    public function test_cannot_reschedule_to_same_datetime(): void
    {
        // Arrange
        $originalDateTime = $this->nextBusinessDay(3);
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $originalDateTime]);

        // Usar la fecha guardada en la BD para garantizar que es exactamente igual
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $appointment->scheduled_at,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->expectExceptionMessage('La nueva fecha y hora son iguales a la actual');
        $this->action->execute($appointment, $dto);
    }

    public function test_cannot_reschedule_with_insufficient_notice(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $this->nextBusinessDay(5)]);

        $newDateTime = Carbon::now()->addMinutes(30); // Solo 30 minutos de antelación
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->expectExceptionMessage('al menos');
        $this->action->execute($appointment, $dto);
    }

    public function test_cannot_reschedule_outside_business_hours(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $this->nextBusinessDay(3)]);

        // Viernes a las 19:00 (fuera de horario laboral)
        $newDateTime = Carbon::now()->addDays(7)->setHour(19);

        // Asegürarse de que es viernes
        while ($newDateTime->dayOfWeek !== 5) {
            $newDateTime->addDay();
        }

        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->expectExceptionMessage('horario laboral');
        $this->action->execute($appointment, $dto);
    }

    public function test_cannot_reschedule_on_weekend(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['scheduled_at' => $this->nextBusinessDay(3)]);

        // Sábado a las 10:00
        $newDateTime = Carbon::now()->addWeek()->startOfWeek()->addDays(5)->setHour(10);

        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->action->execute($appointment, $dto);
    }

    // ==================== TEST: Notificaciones ====================

    public function test_notifies_client_on_reschedule(): void
    {
        // Arrange
        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['scheduled_at' => $this->nextBusinessDay(3)]);

        $newDateTime = $this->nextBusinessDay(5)->setHour(14);
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act
        $this->action->execute($appointment, $dto);

        // Assert
        Mail::assertQueued(\App\Mail\AppointmentNotification::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });
    }

    public function test_notifies_technician_if_assigned(): void
    {
        // Arrange
        Mail::fake();
        $technician = User::factory()->create();
        $appointment = Appointment::factory()
            ->create([
                'technician_id' => $technician->id,
                'scheduled_at' => $this->nextBusinessDay(3),
            ]);

        $newDateTime = $this->nextBusinessDay(5)->setHour(14);
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act
        $this->action->execute($appointment, $dto);

        // Assert
        Mail::assertQueued(\App\Mail\AppointmentNotification::class, function ($mail) use ($technician) {
            return $mail->hasTo($technician->email);
        });
    }

    public function test_does_not_notify_technician_if_not_assigned(): void
    {
        // Arrange
        Mail::fake();
        $appointment = Appointment::factory()
            ->create([
                'technician_id' => null,
                'scheduled_at' => $this->nextBusinessDay(3),
            ]);

        $newDateTime = $this->nextBusinessDay(5)->setHour(14);
        $dto = new RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime,
        );

        // Act
        $this->action->execute($appointment, $dto);

        // Assert
        // Solo debe haber un email (al cliente)
        Mail::assertQueued(\App\Mail\AppointmentNotification::class, 1);
    }

    /**
     * Helper para obtener un día hábil en el futuro.
     */
    private function nextBusinessDay(int $days = 1, int $hour = 10): Carbon
    {
        $date = Carbon::now()->addDays($days)->setHour($hour);
        while ($date->isWeekend()) {
            $date->addDay();
        }
        return $date;
    }
}

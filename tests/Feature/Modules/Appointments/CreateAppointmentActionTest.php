<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Mail\AppointmentNotification;
use App\Models\Service;
use App\Models\User;
use App\Modules\Appointments\Actions\CreateAppointmentAction;
use App\Modules\Appointments\DTOs\CreateAppointmentDTO;
use App\Modules\Appointments\Exceptions\InvalidAppointmentDateException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateAppointmentActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAppointmentAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->action = $this->app->make(CreateAppointmentAction::class);
    }

    // ==================== TEST: Creación Exitosa ====================

    public function test_can_create_appointment_with_valid_data(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();
        $technician = User::factory()->create();

        $scheduledAt = $this->nextBusinessDay();

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $scheduledAt,
            technicianId: $technician->id,
            notes: 'Primera cita',
        );

        // Act
        $appointment = $this->action->execute($dto);

        // Assert
        $this->assertDatabaseHas('appointments', [
            'user_id' => $client->id,
            'service_id' => $service->id,
            'technician_id' => $technician->id,
            'status' => AppointmentStatus::PENDING->value,
            'notes' => 'Primera cita',
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertTrue($appointment->getStatusEnum() === AppointmentStatus::PENDING);
    }

    public function test_can_create_appointment_without_technician(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();
        $scheduledAt = $this->nextBusinessDay();

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $scheduledAt,
        );

        // Act
        $appointment = $this->action->execute($dto);

        // Assert
        $this->assertNull($appointment->technician_id);
        $this->assertTrue($appointment->getStatusEnum() === AppointmentStatus::PENDING);
    }

    // ==================== TEST: Validaciones ====================

    public function test_cannot_create_appointment_in_past(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();
        $pastDate = Carbon::now()->subDays(1);

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $pastDate,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->expectExceptionMessage('pasado');
        $this->action->execute($dto);
    }

    public function test_cannot_create_appointment_without_advance_notice(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();
        $tooSoon = Carbon::now()->addMinutes(30);

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $tooSoon,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->action->execute($dto);
    }

    public function test_cannot_create_appointment_outside_business_hours(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();

        // Mañana a las 19:00 (fuera de horario)
        $outsideHours = Carbon::now()->addDay()->setHour(19);

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $outsideHours,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->action->execute($dto);
    }

    public function test_cannot_create_appointment_on_weekend(): void
    {
        // Arrange
        $client = User::factory()->create();
        $service = Service::factory()->create();

        // Sábado a las 10:00
        $weekend = Carbon::now()->addWeek()->startOfWeek()->addDays(5)->setHour(10);

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $weekend,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentDateException::class);
        $this->action->execute($dto);
    }

    // ==================== TEST: Notificaciones ====================

    public function test_sends_notification_to_client(): void
    {
        // Arrange
        Mail::fake();
        $client = User::factory()->create();
        $service = Service::factory()->create();

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $this->nextBusinessDay(),
        );

        // Act
        $this->action->execute($dto);

        // Assert
        Mail::assertQueued(AppointmentNotification::class, function ($mail) use ($client) {
            return $mail->hasTo($client->email);
        });
    }

    public function test_sends_notification_to_assigned_technician(): void
    {
        // Arrange
        Mail::fake();
        $client = User::factory()->create();
        $technician = User::factory()->create();
        $service = Service::factory()->create();

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $this->nextBusinessDay(),
            technicianId: $technician->id,
        );

        // Act
        $this->action->execute($dto);

        // Assert
        Mail::assertQueued(AppointmentNotification::class, function ($mail) use ($technician) {
            return $mail->hasTo($technician->email);
        });
    }

    public function test_does_not_send_notification_to_unassigned(): void
    {
        // Arrange
        Mail::fake();
        $client = User::factory()->create();
        $service = Service::factory()->create();

        $dto = new CreateAppointmentDTO(
            userId: $client->id,
            serviceId: $service->id,
            scheduledAt: $this->nextBusinessDay(),
        );

        // Act
        $this->action->execute($dto);

        // Assert
        // Solo debe haber una notificación (al cliente)
        Mail::assertQueued(AppointmentNotification::class, 1);
    }

    /**
     * Helper que produce una fecha laboral válida en el futuro.
     */
    private function nextBusinessDay(int $minDays = 1, int $hour = 10): Carbon
    {
        $date = Carbon::now()->addDays($minDays)->setHour($hour);
        while ($date->isWeekend()) {
            $date->addDay();
        }
        return $date;
    }
}

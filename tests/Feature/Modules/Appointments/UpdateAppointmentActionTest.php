<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Modules\Appointments\Actions\UpdateAppointmentAction;
use App\Modules\Appointments\DTOs\UpdateAppointmentDTO;
use App\Modules\Appointments\Exceptions\InvalidAppointmentStatusTransitionException;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UpdateAppointmentActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAppointmentAction $action;
    private AppointmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->repository = $this->app->make(AppointmentRepository::class);
        $this->action = $this->app->make(UpdateAppointmentAction::class);
    }

    // ==================== TEST: Transiciones Válidas ====================

    public function test_can_transition_from_pending_to_confirmed(): void
    {
        // Arrange
        $client = User::factory()->create();
        $technician = User::factory()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->for($service)
            ->create(['status' => AppointmentStatus::PENDING->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
            technicianId: $technician->id,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertTrue($result->getStatusEnum() === AppointmentStatus::CONFIRMED);
        $this->assertEquals($technician->id, $result->technician_id);
        Mail::assertQueued(AppointmentNotification::class);
    }

    public function test_can_transition_from_confirmed_to_in_progress(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::CONFIRMED->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::IN_PROGRESS,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertTrue($result->getStatusEnum() === AppointmentStatus::IN_PROGRESS);
    }

    public function test_can_transition_from_in_progress_to_completed(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::IN_PROGRESS->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::COMPLETED,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertTrue($result->getStatusEnum() === AppointmentStatus::COMPLETED);
    }

    // ==================== TEST: Transiciones Inválidas ====================

    public function test_cannot_transition_from_pending_to_completed_directly(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::COMPLETED,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentStatusTransitionException::class);
        $this->action->execute($appointment, $dto);
    }

    public function test_cannot_transition_from_completed(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::COMPLETED->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
        );

        // Act & Assert
        $this->expectException(InvalidAppointmentStatusTransitionException::class);
        $this->action->execute($appointment, $dto);
    }

    // ==================== TEST: Validaciones de Negocio ====================

    public function test_requires_technician_when_confirming_unassigned_appointment(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create([
                'status' => AppointmentStatus::PENDING->value,
                'technician_id' => null,
            ]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
            technicianId: null,
        );

        // Act & Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Debe asignar un técnico antes de confirmar la cita.');
        $this->action->execute($appointment, $dto);
    }

    public function test_allows_confirming_when_already_assigned(): void
    {
        // Arrange
        $technician = User::factory()->create();
        $appointment = Appointment::factory()
            ->create([
                'status' => AppointmentStatus::PENDING->value,
                'technician_id' => $technician->id,
            ]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertTrue($result->getStatusEnum() === AppointmentStatus::CONFIRMED);
    }

    // ==================== TEST: Actualización de Datos ====================

    public function test_updates_notes_correctly(): void
    {
        // Arrange
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        $newNotes = 'Cita importante - Cliente VIP';
        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
            technicianId: User::factory()->create()->id,
            notes: $newNotes,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertEquals($newNotes, $result->notes);
    }

    public function test_updates_technician_correctly(): void
    {
        // Arrange
        $oldTechnician = User::factory()->create();
        $newTechnician = User::factory()->create();

        $appointment = Appointment::factory()
            ->create([
                'status' => AppointmentStatus::IN_PROGRESS->value,
                'technician_id' => $oldTechnician->id,
            ]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::IN_PROGRESS,
            technicianId: $newTechnician->id,
        );

        // Act
        $result = $this->action->execute($appointment, $dto);

        // Assert
        $this->assertEquals($newTechnician->id, $result->technician_id);
    }

    // ==================== TEST: Notificaciones ====================

    public function test_sends_confirmation_notification_on_pending_to_confirmed(): void
    {
        // Arrange
        Mail::fake();
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
            technicianId: User::factory()->create()->id,
        );

        // Act
        $this->action->execute($appointment, $dto);

        // Assert
        Mail::assertQueued(AppointmentNotification::class, function ($mail) use ($appointment) {
            return $mail->hasTo($appointment->client->email);
        });
    }

    public function test_sends_cancellation_notification(): void
    {
        // Arrange
        Mail::fake();
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::CONFIRMED->value]);

        $dto = new UpdateAppointmentDTO(
            status: AppointmentStatus::CANCELLED,
        );

        // Act
        $this->action->execute($appointment, $dto);

        // Assert
        Mail::assertQueued(AppointmentNotification::class);
    }
}

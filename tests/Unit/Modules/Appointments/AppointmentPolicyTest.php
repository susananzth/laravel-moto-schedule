<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use Tests\TestCase;

class AppointmentPolicyTest extends TestCase
{
    private AppointmentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(AppointmentPolicy::class);
    }

    // ==================== TEST: View All (Admin) ====================

    public function test_admin_can_view_all_appointments(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.view_all');

        // Act
        $result = $this->policy->viewAll($admin);

        // Assert
        $this->assertTrue($result);
    }

    public function test_non_admin_cannot_view_all(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->policy->viewAll($user);

        // Assert
        $this->assertFalse($result);
    }

    // ==================== TEST: View Single Appointment ====================

    public function test_client_can_view_own_appointment(): void
    {
        // Arrange
        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create();

        // Act
        $result = $this->policy->view($client, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_client_cannot_view_other_clients_appointment(): void
    {
        // Arrange
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client1, 'client')
            ->create();

        // Act
        $result = $this->policy->view($client2, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_assigned_technician_can_view_their_appointment(): void
    {
        // Arrange
        $technician = User::factory()->create();
        $technician->givePermissionTo('appointments.be_assigned');

        $appointment = Appointment::factory()
            ->create(['technician_id' => $technician->id]);

        // Act
        $result = $this->policy->view($technician, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_unassigned_technician_cannot_view_appointment(): void
    {
        // Arrange
        $technician1 = User::factory()->create();
        $technician1->givePermissionTo('appointments.be_assigned');

        $technician2 = User::factory()->create();
        $technician2->givePermissionTo('appointments.be_assigned');

        $appointment = Appointment::factory()
            ->create(['technician_id' => $technician1->id]);

        // Act
        $result = $this->policy->view($technician2, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_admin_can_view_any_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.view_all');

        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create();

        // Act
        $result = $this->policy->view($admin, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    // ==================== TEST: Update ====================

    public function test_admin_can_update_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->update($admin, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_non_admin_cannot_update_appointment(): void
    {
        // Arrange
        $user = User::factory()->create();
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->update($user, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_cannot_update_completed_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::COMPLETED->value]);

        // Act
        $result = $this->policy->update($admin, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_cannot_update_cancelled_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::CANCELLED->value]);

        // Act
        $result = $this->policy->update($admin, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    // ==================== TEST: Cancel ====================

    public function test_admin_can_cancel_any_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::CONFIRMED->value]);

        // Act
        $result = $this->policy->cancel($admin, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_client_can_cancel_own_pending_appointment(): void
    {
        // Arrange
        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->cancel($client, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_client_cannot_cancel_own_confirmed_appointment(): void
    {
        // Arrange
        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['status' => AppointmentStatus::CONFIRMED->value]);

        // Act
        $result = $this->policy->cancel($client, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_client_cannot_cancel_completed_appointment(): void
    {
        // Arrange
        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['status' => AppointmentStatus::COMPLETED->value]);

        // Act
        $result = $this->policy->cancel($client, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    // ==================== TEST: Reschedule ====================

    public function test_admin_can_reschedule_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->reschedule($admin, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_client_can_reschedule_own_appointment(): void
    {
        // Arrange
        $client = User::factory()->create();
        $client->givePermissionTo('appointments.edit');

        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->reschedule($client, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_client_cannot_reschedule_other_client_appointment(): void
    {
        // Arrange
        $client1 = User::factory()->create();
        $client1->givePermissionTo('appointments.edit');

        $client2 = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client2, 'client')
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->reschedule($client1, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_cannot_reschedule_completed_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::COMPLETED->value]);

        // Act
        $result = $this->policy->reschedule($admin, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    // ==================== TEST: Assign Technician ====================

    public function test_admin_can_assign_technician(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act
        $result = $this->policy->assignTechnician($admin, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_non_admin_cannot_assign_technician(): void
    {
        // Arrange
        $user = User::factory()->create();
        $appointment = Appointment::factory()->create();

        // Act
        $result = $this->policy->assignTechnician($user, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    // ==================== TEST: Complete ====================

    public function test_assigned_technician_can_complete_appointment(): void
    {
        // Arrange
        $technician = User::factory()->create();
        $technician->givePermissionTo('appointments.be_assigned');

        $appointment = Appointment::factory()
            ->create([
                'technician_id' => $technician->id,
                'status' => AppointmentStatus::IN_PROGRESS->value,
            ]);

        // Act
        $result = $this->policy->complete($technician, $appointment);

        // Assert
        $this->assertTrue($result);
    }

    public function test_technician_cannot_complete_appointment_not_assigned_to_them(): void
    {
        // Arrange
        $technician1 = User::factory()->create();
        $technician1->givePermissionTo('appointments.be_assigned');

        $technician2 = User::factory()->create();

        $appointment = Appointment::factory()
            ->create([
                'technician_id' => $technician1->id,
                'status' => AppointmentStatus::IN_PROGRESS->value,
            ]);

        // Act
        $result = $this->policy->complete($technician2, $appointment);

        // Assert
        $this->assertFalse($result);
    }

    public function test_technician_cannot_complete_already_completed_appointment(): void
    {
        // Arrange
        $technician = User::factory()->create();
        $technician->givePermissionTo('appointments.be_assigned');

        $appointment = Appointment::factory()
            ->create([
                'technician_id' => $technician->id,
                'status' => AppointmentStatus::COMPLETED->value,
            ]);

        // Act
        $result = $this->policy->complete($technician, $appointment);

        // Assert
        $this->assertFalse($result);
    }
}

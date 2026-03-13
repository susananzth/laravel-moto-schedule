<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Livewire\Appointments\AppointmentCalendar;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AppointmentCalendarComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['appointments.view_all', 'appointments.be_assigned', 'appointments.assign'] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }
    }

    // ==================== TEST: Montaje del Componente ====================

    public function test_component_mounts_successfully(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo('appointments.view_all');

        // Act & Assert
        Livewire::actingAs($user)
            ->test(AppointmentCalendar::class)
            ->assertStatus(200);
    }

    public function test_component_renders_without_errors(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo('appointments.view_all');

        // Act & Assert
        Livewire::actingAs($user)
            ->test(AppointmentCalendar::class)
            ->assertViewIs('livewire.appointments.appointment-calendar');
    }

    // ==================== TEST: getEvents() ====================

    public function test_admin_gets_all_events(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.view_all');

        $client1 = User::factory()->create();
        $client2 = User::factory()->create();

        $appointment1 = Appointment::factory()
            ->for($client1, 'client')
            ->create(['scheduled_at' => Carbon::now()->addDays(1)]);

        $appointment2 = Appointment::factory()
            ->for($client2, 'client')
            ->create(['scheduled_at' => Carbon::now()->addDays(2)]);

        // Act
        $component = Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->call('getEvents');

        // Assert
        $this->assertCount(2, $component->get('events'));
    }

    public function test_technician_only_gets_assigned_events(): void
    {
        // Arrange
        $technician = User::factory()->create();
        $technician->givePermissionTo('appointments.be_assigned');

        $otherTechnician = User::factory()->create();
        $client = User::factory()->create();

        // Cita asignada al técnico
        Appointment::factory()
            ->for($client, 'client')
            ->create([
                'technician_id' => $technician->id,
                'scheduled_at' => Carbon::now()->addDays(1),
            ]);

        // Cita asignada a otro técnico
        Appointment::factory()
            ->for($client, 'client')
            ->create([
                'technician_id' => $otherTechnician->id,
                'scheduled_at' => Carbon::now()->addDays(2),
            ]);

        // Act
        $component = Livewire::actingAs($technician)
            ->test(AppointmentCalendar::class)
            ->call('getEvents');

        // Assert
        $this->assertCount(1, $component->get('events'));
    }

    public function test_client_only_gets_own_events(): void
    {
        // Arrange
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();

        // Cita del cliente 1
        Appointment::factory()
            ->for($client1, 'client')
            ->create(['scheduled_at' => Carbon::now()->addDays(1)]);

        // Cita del cliente 2
        Appointment::factory()
            ->for($client2, 'client')
            ->create(['scheduled_at' => Carbon::now()->addDays(2)]);

        // Act
        $component = Livewire::actingAs($client1)
            ->test(AppointmentCalendar::class)
            ->call('getEvents');

        // Assert
        $this->assertCount(1, $component->get('events'));
    }

    // ==================== TEST: editAppointment() ====================

    public function test_admin_can_edit_any_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.view_all');
        $admin->givePermissionTo('appointments.assign');

        $client = User::factory()->create();
        $appointment = Appointment::factory()
            ->for($client, 'client')
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act & Assert
        // Solo verificar que el policy permite ver la cita para el admin
        $policy = app(\App\Modules\Appointments\Policies\AppointmentPolicy::class);
        $this->assertTrue($policy->view($admin, $appointment));
        $this->assertTrue($policy->update($admin, $appointment));
    }

    public function test_client_cannot_edit_other_client_appointment(): void
    {
        // Arrange
        $client1 = User::factory()->create();
        $client2 = User::factory()->create();

        $appointment = Appointment::factory()
            ->for($client1, 'client')
            ->create();

        // Act & Assert - Debería fallar por autorización
        Livewire::actingAs($client2)
            ->test(AppointmentCalendar::class)
            ->call('editAppointment', $appointment->id)
            ->assertDispatched('app-error');
    }

    public function test_cannot_edit_completed_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.view_all');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::COMPLETED->value]);

        // Act & Assert
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->call('editAppointment', $appointment->id)
            ->assertDispatched('app-error');
    }

    // ==================== TEST: updateAppointment() ====================

    public function test_admin_can_update_appointment_to_confirmed(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');
        $admin->givePermissionTo('appointments.view_all');

        $technician = User::factory()->create();
        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // El update se hace a través de la acción, no el componente directamente
        // Ya que el componente solo coordina
        $action = app(\App\Modules\Appointments\Actions\UpdateAppointmentAction::class);
        $dto = new \App\Modules\Appointments\DTOs\UpdateAppointmentDTO(
            status: AppointmentStatus::CONFIRMED,
            technicianId: $technician->id,
        );

        // Act
        $result = $action->execute($appointment, $dto);

        // Assert
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatus::CONFIRMED->value,
            'technician_id' => $technician->id,
        ]);
        $this->assertTrue($result->getStatusEnum() === AppointmentStatus::CONFIRMED);
    }

    public function test_cannot_confirm_without_technician(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create([
                'status' => AppointmentStatus::PENDING->value,
                'technician_id' => null,
            ]);

        // Act & Assert
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->set('selectedAppointment', $appointment)
            ->set('status', AppointmentStatus::CONFIRMED->value)
            ->set('technician_id', null)
            ->call('updateAppointment')
            ->assertDispatched('app-error');
    }

    public function test_invalid_status_transition_fails(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['status' => AppointmentStatus::PENDING->value]);

        // Act & Assert - Transición directa a COMPLETED no es permitida
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->set('selectedAppointment', $appointment)
            ->set('status', AppointmentStatus::COMPLETED->value)
            ->call('updateAppointment')
            ->assertDispatched('app-error');
    }

    // ==================== TEST: updateAppointmentDate() (Drag & Drop) ====================

    public function test_admin_can_reschedule_appointment(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');
        $admin->givePermissionTo('appointments.view_all');

        $appointment = Appointment::factory()
            ->create(['scheduled_at' => Carbon::now()->addDays(3)->setHour(10)->setMinute(0)->setSecond(0)]);

        $newDateTime = Carbon::now()->addDays(5)->setHour(14)->setMinute(0)->setSecond(0);

        // El reschedule se hace a través de la acción
        $action = app(\App\Modules\Appointments\Actions\RescheduleAppointmentAction::class);
        $dto = new \App\Modules\Appointments\DTOs\RescheduleAppointmentDTO(
            appointmentId: $appointment->id,
            newScheduledAt: $newDateTime->clone()->setMinute(0)->setSecond(0),
        );

        // Act
        $result = $action->execute($appointment, $dto);

        // Assert
        $this->assertEquals($newDateTime->clone()->setMinute(0)->setSecond(0)->format('Y-m-d H:i'),
                           $result->scheduled_at->format('Y-m-d H:i'));
    }

    public function test_cannot_reschedule_to_past_date(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->givePermissionTo('appointments.assign');

        $appointment = Appointment::factory()
            ->create(['scheduled_at' => Carbon::now()->addDays(7)]);

        $pastDateTime = Carbon::now()->subDays(1);

        // Act & Assert
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->call('updateAppointmentDate', $appointment->id, $pastDateTime->toDateTimeString())
            ->assertDispatched('app-error');
    }

    // ==================== TEST: Modal State ====================

    public function test_modal_state_changes_on_show(): void
    {
        // Arrange
        $admin = User::factory()->create();

        // Act & Assert
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->set('showModal', true)
            ->assertSet('showModal', true)
            ->assertDispatched('show-modal-changed');
    }

    public function test_modal_state_changes_on_hide(): void
    {
        // Arrange
        $admin = User::factory()->create();

        // Act & Assert
        Livewire::actingAs($admin)
            ->test(AppointmentCalendar::class)
            ->set('showModal', true)
            ->set('showModal', false)
            ->assertSet('showModal', false);
    }
}

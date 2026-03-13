<?php

declare(strict_types=1);

namespace App\Livewire\Appointments;

use App\Core\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Modules\Appointments\Actions\GetCalendarEventsAction;
use App\Modules\Appointments\Actions\RescheduleAppointmentAction;
use App\Modules\Appointments\Actions\UpdateAppointmentAction;
use App\Modules\Appointments\DTOs\RescheduleAppointmentDTO;
use App\Modules\Appointments\DTOs\UpdateAppointmentDTO;
use App\Modules\Appointments\Exceptions\InvalidAppointmentDateException;
use App\Modules\Appointments\Exceptions\InvalidAppointmentStatusTransitionException;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

final class AppointmentCalendar extends Component
{
    use AuthorizesRequests;

    public ?Appointment $selectedAppointment = null;
    public ?int $technician_id = null;
    public ?string $status = null;
    public string $adminNotes = '';
    public bool $showModal = false;
    public array $events = [];

    public function __construct(
        ?UpdateAppointmentAction $updateAppointmentAction = null,
        ?RescheduleAppointmentAction $rescheduleAppointmentAction = null,
        ?GetCalendarEventsAction $getCalendarEventsAction = null,
        ?AppointmentPolicy $appointmentPolicy = null,
    ) {
        // Livewire instantiates without constructor args during view rendering, so fallback
        $this->updateAppointmentAction = $updateAppointmentAction ?? app()->make(UpdateAppointmentAction::class);
        $this->rescheduleAppointmentAction = $rescheduleAppointmentAction ?? app()->make(RescheduleAppointmentAction::class);
        $this->getCalendarEventsAction = $getCalendarEventsAction ?? app()->make(GetCalendarEventsAction::class);
        $this->appointmentPolicy = $appointmentPolicy ?? app()->make(AppointmentPolicy::class);
    }

    protected function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', array_map(
                    fn (AppointmentStatus $status) => $status->value,
                    AppointmentStatus::cases()
                )),
            ],
            'technician_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'adminNotes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'status.required' => 'El estado es requerido.',
            'status.in' => 'El estado seleccionado no es válido.',
            'technician_id.exists' => 'El técnico seleccionado no existe.',
            'adminNotes.max' => 'Las notas no pueden exceder 500 caracteres.',
        ];
    }

    public function updatedShowModal(): void
    {
        $this->dispatch('show-modal-changed', value: $this->showModal);
    }

    /**
     * Carga eventos del calendario para un rango de fechas.
     */
    #[On('refresh-calendar')]
    public function getEvents(): void
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            $this->events = [];
            return;
        }

        $appointments = $this->getCalendarEventsAction->execute(
            $user,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        $this->events = $appointments->map(fn ($event) => $event->toArray())->all();
    }

    /**
     * Actualiza una cita con los datos del formulario.
     *
     * @throws AuthorizationException
     */
    public function updateAppointment(): void
    {
        $this->validate();

        if (!$this->selectedAppointment instanceof Appointment) {
            $this->dispatch('app-error', message: 'No hay cita seleccionada.');
            return;
        }

        $user = auth()->user();
        if (!$user instanceof User) {
            $this->dispatch('app-error', message: 'Usuario no autenticado.');
            return;
        }

        // Autorizar
        try {
            $this->authorize('update', $this->selectedAppointment);
        } catch (AuthorizationException) {
            $this->dispatch('app-error', message: 'No tienes permiso para actualizar esta cita.');
            return;
        }

        try {
            $dto = new UpdateAppointmentDTO(
                status: AppointmentStatus::from($this->status),
                technicianId: $this->technician_id,
                notes: $this->adminNotes ?: null,
            );

            $this->updateAppointmentAction->execute($this->selectedAppointment, $dto);

            $this->showModal = false;
            $this->dispatch('close-modal');
            $this->dispatch('refresh-calendar');
            $this->dispatch('notify', message: 'Cita actualizada correctamente.');
        } catch (InvalidAppointmentStatusTransitionException $e) {
            $this->dispatch('app-error', message: $e->getMessage());
        } catch (\DomainException $e) {
            $this->dispatch('app-error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('app-error', message: 'Error al actualizar la cita: ' . $e->getMessage());
        }
    }

    /**
     * Edita una cita (carga modal).
     *
     * @throws AuthorizationException
     */
    public function editAppointment(int $id): void
    {
        $appointment = Appointment::findOrFail($id);
        $user = auth()->user();

        if (!$user instanceof User) {
            $this->dispatch('app-error', message: 'Usuario no autenticado.');
            return;
        }

        // Autorizar usando Policy
        try {
            $this->authorize('view', $appointment);
        } catch (AuthorizationException) {
            $this->dispatch('app-error', message: 'No tienes permiso para ver esta cita.');
            return;
        }

        // Validaciones de negocio
        if ($appointment->isCompletedOrCancelled()) {
            $this->dispatch('app-error', message: 'No se puede modificar una cita ' . $appointment->getStatusEnum()->label() . '.');
            return;
        }

        $this->selectedAppointment = $appointment;
        $this->technician_id = $appointment->technician_id;
        $this->status = $appointment->status;
        $this->adminNotes = $appointment->notes ?? '';
        $this->showModal = true;

        $this->dispatch('open-modal', name: 'admin-appointment-manager');
    }

    /**
     * Reprograma una cita a una nueva fecha (Drag & Drop).
     *
     * @throws AuthorizationException
     */
    public function updateAppointmentDate(int $id, string $newDate): void
    {
        $appointment = Appointment::findOrFail($id);
        $user = auth()->user();

        if (!$user instanceof User) {
            $this->dispatch('app-error', message: 'Usuario no autenticado.');
            return;
        }

        // Autorizar
        try {
            $this->authorize('reschedule', $appointment);
        } catch (AuthorizationException) {
            $this->dispatch('app-error', message: 'No tienes permiso para reprogramar esta cita.');
            return;
        }

        try {
            $newDateTime = Carbon::parse($newDate);

            $dto = new RescheduleAppointmentDTO(
                appointmentId: $id,
                newScheduledAt: $newDateTime,
            );

            $this->rescheduleAppointmentAction->execute($appointment, $dto);

            $this->dispatch('notify', message: 'Cita reprogramada correctamente.');
            $this->dispatch('refresh-calendar');
        } catch (InvalidAppointmentDateException $e) {
            $this->dispatch('app-error', message: $e->getMessage());
            $this->dispatch('refresh-calendar'); // Revertir visualmente
        } catch (\Throwable $e) {
            $this->dispatch('app-error', message: 'Error al reprogramar: ' . $e->getMessage());
            $this->dispatch('refresh-calendar');
        }
    }

    public function render()
    {
        $technicians = User::permission('appointments.be_assigned')->get();

        return view('livewire.appointments.appointment-calendar', [
            'technicians' => $technicians,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Repositories\UserRepository;
use App\Modules\Appointments\Actions\CreateAppointmentAction;
use App\Modules\Appointments\Actions\GetCalendarEventsAction;
use App\Modules\Appointments\Actions\RescheduleAppointmentAction;
use App\Modules\Appointments\Actions\UpdateAppointmentAction;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     * Este método se ejecuta cuando Laravel bootstrapea el contenedor.
     */
    public function register(): void
    {
        // ==================== Repositories ====================
        $this->app->singleton(AppointmentRepository::class);
        $this->app->singleton(UserRepository::class);

        // ==================== Actions (Appointments) ====================
        $this->app->singleton(CreateAppointmentAction::class);
        $this->app->singleton(UpdateAppointmentAction::class);
        $this->app->singleton(RescheduleAppointmentAction::class);
        $this->app->singleton(GetCalendarEventsAction::class);

        // ==================== Policies ====================
        $this->app->singleton(AppointmentPolicy::class);
    }

    /**
     * Boostrapea los servicios de la aplicación.
     * Se ejecuta después de que todos los servicios han sido registrados.
     */
    public function boot(): void
    {
        // Registrar Policies explícitamente si es necesario
        // (Laravel también las autodescubre por convención)
    }
}

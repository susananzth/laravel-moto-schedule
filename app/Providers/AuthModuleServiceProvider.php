<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Repositories\UserRepository;
use App\Models\User;
use App\Modules\Auth\Actions\ForgotPasswordAction;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\Actions\ResetPasswordAction;
use App\Modules\Auth\Policies\UserPolicy;
use App\Modules\Appointments\Actions\CreateAppointmentAction;
use App\Modules\Appointments\Actions\GetCalendarEventsAction;
use App\Modules\Appointments\Actions\RescheduleAppointmentAction;
use App\Modules\Appointments\Actions\UpdateAppointmentAction;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use App\Core\Repositories\AppointmentRepository;
use Illuminate\Support\ServiceProvider;

final class AppModuleServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     */
    public function register(): void
    {
        // Registrar Repositories
        $this->registerRepositories();

        // Registrar Actions
        $this->registerActions();

        // Registrar Policies
        $this->registerPolicies();
    }

    /**
     * Registra las repositories en el contenedor.
     */
    private function registerRepositories(): void
    {
        $this->app->singleton(UserRepository::class, function () {
            return new UserRepository();
        });

        $this->app->singleton(AppointmentRepository::class, function () {
            return new AppointmentRepository();
        });
    }

    /**
     * Registra las actions en el contenedor.
     */
    private function registerActions(): void
    {
        // Auth Actions
        $this->app->singleton(LoginAction::class, function () {
            return new LoginAction(
                $this->app->make(UserRepository::class),
            );
        });

        $this->app->singleton(RegisterAction::class, function () {
            return new RegisterAction(
                $this->app->make(UserRepository::class),
            );
        });

        $this->app->singleton(ForgotPasswordAction::class, function () {
            return new ForgotPasswordAction(
                $this->app->make(UserRepository::class),
            );
        });

        $this->app->singleton(ResetPasswordAction::class, function () {
            return new ResetPasswordAction(
                $this->app->make(UserRepository::class),
            );
        });

        $this->app->singleton(LogoutAction::class, function () {
            return new LogoutAction(
                $this->app->make(UserRepository::class),
            );
        });

        // Appointments Actions
        $this->app->singleton(CreateAppointmentAction::class, function () {
            return new CreateAppointmentAction(
                $this->app->make(AppointmentRepository::class),
            );
        });

        $this->app->singleton(UpdateAppointmentAction::class, function () {
            return new UpdateAppointmentAction(
                $this->app->make(AppointmentRepository::class),
            );
        });

        $this->app->singleton(RescheduleAppointmentAction::class, function () {
            return new RescheduleAppointmentAction(
                $this->app->make(AppointmentRepository::class),
            );
        });

        $this->app->singleton(GetCalendarEventsAction::class, function () {
            return new GetCalendarEventsAction(
                $this->app->make(AppointmentRepository::class),
            );
        });
    }

    /**
     * Registra las policies en el contenedor.
     */
    private function registerPolicies(): void
    {
        // Registrar en el Gate
        $this->app['auth']->provider('eloquent')->model(User::class);
    }
}

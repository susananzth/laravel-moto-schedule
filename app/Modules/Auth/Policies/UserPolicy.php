<?php

declare(strict_types=1);

namespace App\Modules\Auth\Policies;

use App\Models\User;

final class UserPolicy
{
    /**
     * Determina si el usuario puede actualizar su perfil.
     */
    public function updateProfile(User $user, User $targetUser): bool
    {
        // El usuario puede actualizar su propio perfil
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin puede actualizar cualquier perfil
        return $user->hasRole('Admin');
    }

    /**
     * Determina si el usuario puede cambiar su contraseña.
     */
    public function changePassword(User $user, User $targetUser): bool
    {
        // El usuario puede cambiar su propia contraseña
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin puede cambiar contraseña de otros
        return $user->hasRole('Admin');
    }

    /**
     * Determina si el usuario puede ver otro usuario.
     */
    public function view(User $user, User $targetUser): bool
    {
        // El usuario puede verse a sí mismo
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin puede ver cualquiera
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Técnico puede ver detalles de clientes para citas
        if ($user->hasRole('Técnico')) {
            // Verificar si tienen citas en común
            return $user->assignedAppointments()
                ->where('client_id', $targetUser->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar su cuenta.
     */
    public function deleteAccount(User $user, User $targetUser): bool
    {
        // El usuario puede eliminar su propia cuenta
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Admin puede eliminar cuentas
        return $user->hasRole('Admin');
    }
}

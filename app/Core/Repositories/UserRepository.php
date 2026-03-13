<?php

declare(strict_types=1);

namespace App\Core\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

final class UserRepository
{
    /**
     * Obtiene todos los técnicos disponibles.
     *
     * @return Collection<int, User>
     */
    public function getTechnicians(): Collection
    {
        return User::permission('appointments.be_assigned')->get();
    }

    /**
     * Obtiene un usuario por ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Obtiene un usuario por email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', strtolower($email))->first();
    }

    /**
     * Obtiene un usuario por username.
     */
    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    /**
     * Verifica si un usuario tiene un permiso específico.
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $user = $this->findById($userId);
        return $user?->hasPermissionTo($permission) ?? false;
    }

    /**
     * Crea un nuevo usuario con sus datos iniciales.
     */
    public function createUser(
        string $firstname,
        string $lastname,
        string $username,
        string $phone,
        string $email,
        string $password,
    ): User {
        return User::create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => $username,
            'phone' => $phone,
            'email' => strtolower($email),
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Guarda un usuario (actualiza).
     */
    public function save(User $user): User
    {
        $user->save();
        return $user->refresh();
    }
}

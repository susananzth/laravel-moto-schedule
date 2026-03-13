<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Core\Repositories\UserRepository;
use App\Models\User;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Exceptions\InvalidEmailException;
use App\Modules\Auth\Exceptions\UserAlreadyExistsException;
use Illuminate\Auth\Events\Registered;

final readonly class RegisterAction
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Registra un nuevo usuario.
     *
     * @throws UserAlreadyExistsException
     * @throws InvalidEmailException
     */
    public function execute(RegisterDTO $dto): User
    {
        // 1. Validar que el email no exista
        if ($this->userRepository->findByEmail($dto->email)) {
            throw UserAlreadyExistsException::withEmail($dto->email);
        }

        // 2. Validar que el username no exista
        if ($this->userRepository->findByUsername($dto->username)) {
            throw UserAlreadyExistsException::withUsername($dto->username);
        }

        // 3. Crear usuario
        $user = $this->userRepository->createUser(
            firstname: $dto->firstname,
            lastname: $dto->lastname,
            username: $dto->username,
            phone: $dto->phone,
            email: $dto->email,
            password: $dto->password,
        );

        // 4. Disparar evento de registro (envía email de bienvenida/verificación)
        event(new Registered($user));

        // 5. Asignar rol de cliente
        $user->assignRole('Cliente');

        return $user;
    }
}

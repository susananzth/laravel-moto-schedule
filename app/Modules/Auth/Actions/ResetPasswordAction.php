<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Core\Repositories\UserRepository;
use App\Modules\Auth\DTOs\ResetPasswordDTO;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Resetea la contraseña del usuario.
     */
    public function execute(ResetPasswordDTO $dto): bool
    {
        $status = Password::reset([
            'email' => $dto->email,
            'password' => $dto->password,
            'password_confirmation' => $dto->password,
            'token' => $dto->token,
        ], function ($user) use ($dto) {
            // Actualizar contraseña y token de remembrance
            $user->forceFill([
                'password' => Hash::make($dto->password),
                'remember_token' => Str::random(60),
            ])->save();

            // Disparar evento de password reset
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET;
    }
}

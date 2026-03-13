<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Core\Repositories\UserRepository;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use App\Modules\Auth\Exceptions\PasswordResetLinkException;
use Illuminate\Support\Facades\Password;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Envía un link de restablecimiento de contraseña.
     *
     * @throws PasswordResetLinkException
     */
    public function execute(ForgotPasswordDTO $dto): void
    {
        // 1. Verificar que la cuenta existe
        $user = $this->userRepository->findByEmail($dto->email);
        if (! $user) {
            throw PasswordResetLinkException::make();
        }

        // 2. Enviar link de reset
        $status = Password::sendResetLink([
            'email' => $dto->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw PasswordResetLinkException::make();
        }
    }
}

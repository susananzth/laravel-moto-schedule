<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Core\Repositories\UserRepository;
use App\Modules\Auth\DTOs\LogoutDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final readonly class LogoutAction
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Cierra la sesión del usuario.
     */
    public function execute(LogoutDTO $dto): void
    {
        // 1. Cerrar sesión
        Auth::guard('web')->logout();

        // 2. Invalidar sesión (previene Session Fixation)
        Session::invalidate();

        // 3. Regenerar token CSRF
        Session::regenerateToken();
    }
}

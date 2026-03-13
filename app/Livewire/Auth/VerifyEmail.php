<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\DTOs\LogoutDTO;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class VerifyEmail extends Component
{
    public function __construct(
        private readonly LogoutAction $logoutAction,
    ) {}

    /**
     * Envía notificación de verificación de email.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect('/', navigate: true);
            return;
        }

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
            return;
        }

        $user->sendEmailVerificationNotification();

        session()->flash('status', 'verification-link-sent');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            $dto = new LogoutDTO(userId: $user->id);
            $this->logoutAction->execute($dto);
        }

        $this->redirect('/', navigate: true);
    }
}

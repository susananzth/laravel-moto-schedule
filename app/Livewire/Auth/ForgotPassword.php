<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\ForgotPasswordAction;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use App\Modules\Auth\Exceptions\PasswordResetLinkException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class ForgotPassword extends Component
{
    public string $email = '';

    private ForgotPasswordAction $forgotPasswordAction;

    public function __construct(ForgotPasswordAction $forgotPasswordAction = null)
    {
        $this->forgotPasswordAction = $forgotPasswordAction ?? app(ForgotPasswordAction::class);
    }

    /**
     * Envía un link de restablecimiento de contraseña.
     */
    public function sendPasswordResetLink(): void
    {
        try {
            $this->validate([
                'email' => ['required', 'string', 'email'],
            ]);

            $dto = new ForgotPasswordDTO(email: $this->email);

            $this->forgotPasswordAction->execute($dto);

            session()->flash('status', __('Se enviará un enlace de restablecimiento si la cuenta existe.'));
        } catch (PasswordResetLinkException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }
    }
}

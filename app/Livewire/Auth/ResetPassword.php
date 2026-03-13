<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\ResetPasswordAction;
use App\Modules\Auth\DTOs\ResetPasswordDTO;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function __construct(
        private readonly ResetPasswordAction $resetPasswordAction,
    ) {}

    /**
     * Monta el componente con el token de reset.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email')->value();
    }

    /**
     * Resetea la contraseña del usuario.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $dto = new ResetPasswordDTO(
            token: $this->token,
            email: $this->email,
            password: $this->password,
        );

        $success = $this->resetPasswordAction->execute($dto);

        if (! $success) {
            $this->addError('email', __('passwords.user'));
            return;
        }

        session()->flash('status', __('passwords.reset'));
        $this->redirectRoute('login', navigate: true);
    }
}

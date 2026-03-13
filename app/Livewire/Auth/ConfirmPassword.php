<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class ConfirmPassword extends Component
{
    public string $password = '';

    private LoginAction $loginAction;

    public function __construct(LoginAction $loginAction = null)
    {
        $this->loginAction = $loginAction ?? app(LoginAction::class);
    }

    /**
     * Confirma la contraseña del usuario actual.
     */
    public function confirmPassword(): void
    {
        try {
            $this->validate([
                'password' => ['required', 'string'],
            ]);

            $user = Auth::user();
            if (! $user) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            // Validar credenciales
            $dto = new LoginDTO(
                email: $user->email,
                password: $this->password,
                remember: false,
            );

            $this->loginAction->execute($dto);

            // Marcar sesión como confirmada
            session(['auth.password_confirmed_at' => time()]);

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } catch (InvalidCredentialsException | TooManyLoginAttemptsException) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }
    }
}

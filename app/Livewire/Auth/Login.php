<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    private LoginAction $loginAction;

    public function __construct(LoginAction $loginAction = null)
    {
        $this->loginAction = $loginAction ?? app(LoginAction::class);
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(): void
    {
        try {
            $this->validate();

            $dto = new LoginDTO(
                email: $this->email,
                password: $this->password,
                remember: $this->remember,
            );

            $this->loginAction->execute($dto);

            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } catch (InvalidCredentialsException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        } catch (TooManyLoginAttemptsException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }
    }
}


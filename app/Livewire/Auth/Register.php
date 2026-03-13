<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Exceptions\UserAlreadyExistsException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
final class Register extends Component
{
    public string $firstname = '';
    public string $lastname = '';
    public string $username = '';
    public string $phone = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    private RegisterAction $registerAction;

    public function __construct(RegisterAction $registerAction = null)
    {
        $this->registerAction = $registerAction ?? app(RegisterAction::class);
    }

    /**
     * Maneja el registro de nuevos usuarios.
     */
    public function register(): void
    {
        try {
            $validated = $this->validate([
                'firstname' => ['required', 'string', 'max:255'],
                'lastname' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            ]);

            $dto = new RegisterDTO(
                firstname: $this->firstname,
                lastname: $this->lastname,
                username: $this->username,
                phone: $this->phone,
                email: $this->email,
                password: $this->password,
            );

            $user = $this->registerAction->execute($dto);

            Auth::login($user);

            $this->redirect(route('dashboard', absolute: false), navigate: true);
        } catch (UserAlreadyExistsException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }
    }
}

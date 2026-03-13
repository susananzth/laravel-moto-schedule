<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Core\Repositories\UserRepository;
use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final readonly class LoginAction
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Autentica un usuario con email y contraseña.
     *
     * @throws InvalidCredentialsException
     * @throws TooManyLoginAttemptsException
     */
    public function execute(LoginDTO $dto): User
    {
        // 1. Validar límite de intentos fallidos
        $this->ensureIsNotRateLimited($dto->email);

        // 2. Intentar autenticar
        if (! Auth::attempt([
            'email' => $dto->email,
            'password' => $dto->password,
        ], $dto->remember)) {
            // Contabilizar intento fallido
            RateLimiter::hit($this->throttleKey($dto->email));

            throw InvalidCredentialsException::make();
        }

        // 3. Limpiar contador de intentos fallidos
        RateLimiter::clear($this->throttleKey($dto->email));

        // 4. Regenerar sesión (CRÍTICO por seguridad)
        Session::regenerate();

        // 5. Retornar usuario autenticado
        return Auth::user();
    }

    /**
     * Verifica que el usuario no esté bloqueado por demasiados intentos.
     *
     * @throws TooManyLoginAttemptsException
     */
    private function ensureIsNotRateLimited(string $email): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($email), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($email));

        throw new TooManyLoginAttemptsException($seconds);
    }

    /**
     * Genera una llave única para el limitador de intentos.
     * Combina email + IP para evitar bloqueos cruzados.
     */
    private function throttleKey(string $email): string
    {
        return Str::transliterate(Str::lower($email) . '|' . request()->ip());
    }
}

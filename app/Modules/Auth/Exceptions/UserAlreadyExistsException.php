<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class UserAlreadyExistsException extends Exception
{
    public static function withEmail(string $email): self
    {
        return new self("User with email {$email} already exists.");
    }

    public static function withUsername(string $username): self
    {
        return new self("User with username {$username} already exists.");
    }
}

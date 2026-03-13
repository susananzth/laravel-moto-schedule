<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class InvalidEmailException extends Exception
{
    public static function make(string $email): self
    {
        return new self("Invalid email format: {$email}");
    }
}

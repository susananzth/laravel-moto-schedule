<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class InvalidCredentialsException extends Exception
{
    public static function make(): self
    {
        return new self(__('auth.failed'));
    }
}

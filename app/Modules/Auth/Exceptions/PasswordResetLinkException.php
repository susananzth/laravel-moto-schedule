<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class PasswordResetLinkException extends Exception
{
    public static function make(): self
    {
        return new self(__('passwords.user'));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use Exception;

final class TooManyLoginAttemptsException extends Exception
{
    public function __construct(
        private readonly int $seconds,
    ) {
        $minutes = (int) ceil($seconds / 60);
        parent::__construct(__('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => $minutes,
        ]));
    }

    public function getAvailableInSeconds(): int
    {
        return $this->seconds;
    }
}

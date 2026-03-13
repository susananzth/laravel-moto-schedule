<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

final readonly class ForgotPasswordDTO
{
    public function __construct(
        public string $email,
    ) {}
}

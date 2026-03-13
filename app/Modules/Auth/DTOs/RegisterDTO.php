<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

final readonly class RegisterDTO
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $username,
        public string $phone,
        public string $email,
        public string $password,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

final readonly class LogoutDTO
{
    public function __construct(
        public int $userId,
    ) {}
}

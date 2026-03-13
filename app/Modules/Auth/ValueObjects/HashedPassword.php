<?php

declare(strict_types=1);

namespace App\Modules\Auth\ValueObjects;

use Illuminate\Support\Facades\Hash;

final readonly class HashedPassword
{
    private string $value;

    public function __construct(string $plainPassword)
    {
        $this->value = Hash::make($plainPassword);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function matches(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

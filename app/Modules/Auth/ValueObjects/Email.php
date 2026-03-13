<?php

declare(strict_types=1);

namespace App\Modules\Auth\ValueObjects;

use App\Modules\Auth\Exceptions\InvalidEmailException;

final readonly class Email
{
    private string $value;

    /**
     * @throws InvalidEmailException
     */
    public function __construct(string $email)
    {
        // normalize by trimming before validation
        $email = trim($email);

        $this->validateEmail($email);
        $this->value = strtolower($email);
    }

    /**
     * @throws InvalidEmailException
     */
    private function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::make($email);
        }

        if (strlen($email) > 255) {
            throw InvalidEmailException::make('Email too long');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}

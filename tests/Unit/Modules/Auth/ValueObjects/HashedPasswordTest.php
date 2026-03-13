<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth\ValueObjects;

use App\Modules\Auth\ValueObjects\HashedPassword;
use PHPUnit\Framework\TestCase;

final class HashedPasswordTest extends TestCase
{
    /**
     * @test
     */
    public function it_hashes_password_on_creation(): void
    {
        // Arrange
        $plainPassword = 'SecurePassword123!';

        // Act
        $hashedPassword = new HashedPassword($plainPassword);

        // Assert
        $this->assertNotEquals($plainPassword, $hashedPassword->getValue());
    }

    /**
     * @test
     */
    public function it_matches_password(): void
    {
        // Arrange
        $plainPassword = 'SecurePassword123!';
        $hashedPassword = new HashedPassword($plainPassword);

        // Act & Assert
        $this->assertTrue($hashedPassword->matches($plainPassword));
    }

    /**
     * @test
     */
    public function it_does_not_match_wrong_password(): void
    {
        // Arrange
        $plainPassword = 'SecurePassword123!';
        $hashedPassword = new HashedPassword($plainPassword);

        // Act & Assert
        $this->assertFalse($hashedPassword->matches('WrongPassword123!'));
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_string(): void
    {
        // Arrange
        $plainPassword = 'SecurePassword123!';
        $hashedPassword = new HashedPassword($plainPassword);

        // Act & Assert
        $this->assertIsString((string) $hashedPassword);
        $this->assertNotEmpty((string) $hashedPassword);
    }
}

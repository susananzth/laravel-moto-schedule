<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth\ValueObjects;

use App\Modules\Auth\Exceptions\InvalidEmailException;
use App\Modules\Auth\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_valid_email(): void
    {
        // Arrange & Act
        $email = new Email('test@example.com');

        // Assert
        $this->assertEquals('test@example.com', $email->getValue());
    }

    /**
     * @test
     */
    public function it_converts_email_to_lowercase(): void
    {
        // Arrange & Act
        $email = new Email('Test@EXAMPLE.COM');

        // Assert
        $this->assertEquals('test@example.com', $email->getValue());
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_format(): void
    {
        // Arrange & Act & Assert
        $this->expectException(InvalidEmailException::class);
        new Email('not-an-email');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_empty_email(): void
    {
        // Arrange & Act & Assert
        $this->expectException(InvalidEmailException::class);
        new Email('');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_email_too_long(): void
    {
        // Arrange
        $longEmail = str_repeat('a', 250) . '@example.com';

        // Act & Assert
        $this->expectException(InvalidEmailException::class);
        new Email($longEmail);
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_string(): void
    {
        // Arrange
        $email = new Email('test@example.com');

        // Act & Assert
        $this->assertEquals('test@example.com', (string) $email);
    }

    /**
     * @test
     */
    public function it_can_compare_emails(): void
    {
        // Arrange
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        // Act & Assert
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    /**
     * @test
     */
    public function it_trims_whitespace(): void
    {
        // Arrange & Act
        $email = new Email('  test@example.com  ');

        // Assert
        $this->assertEquals('test@example.com', $email->getValue());
    }
}

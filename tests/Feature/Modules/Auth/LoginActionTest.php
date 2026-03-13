<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginActionTest extends TestCase
{
    use RefreshDatabase;

    private LoginAction $loginAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAction = $this->app->make(LoginAction::class);
    }

    /**
     * @test
     */
    public function it_successfully_authenticates_valid_credentials(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        $dto = new LoginDTO(
            email: $user->email,
            password: $password,
            remember: false,
        );

        // Act
        $authenticatedUser = $this->loginAction->execute($dto);

        // Assert
        $this->assertTrue($authenticatedUser->is($user));
        $this->assertAuthenticatedAs($authenticatedUser);
    }

    /**
     * @test
     */
    public function it_throws_exception_with_invalid_credentials(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrongpassword',
            remember: false,
        );

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->loginAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_throws_exception_with_non_existent_email(): void
    {
        // Arrange
        $dto = new LoginDTO(
            email: 'nonexistent@example.com',
            password: 'password123',
            remember: false,
        );

        // Act & Assert
        $this->expectException(InvalidCredentialsException::class);
        $this->loginAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_respects_remember_me_flag(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        $dto = new LoginDTO(
            email: $user->email,
            password: $password,
            remember: true,
        );

        // Act
        $authenticatedUser = $this->loginAction->execute($dto);

        // Assert
        $this->assertTrue($authenticatedUser->is($user));
        $this->assertAuthenticatedAs($authenticatedUser);
    }

    /**
     * @test
     */
    public function it_regenerates_session_on_successful_login(): void
    {
        // Arrange
        $oldSessionId = session()->getId();
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        $dto = new LoginDTO(
            email: $user->email,
            password: $password,
            remember: false,
        );

        // Act
        $this->loginAction->execute($dto);
        $newSessionId = session()->getId();

        // Assert
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /**
     * @test
     */
    public function it_blocks_after_too_many_failed_attempts(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrongpassword',
            remember: false,
        );

        // Act - Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->loginAction->execute($dto);
            } catch (InvalidCredentialsException) {
                // Expected
            }
        }

        // Act - 6th attempt should trigger rate limit
        $this->expectException(TooManyLoginAttemptsException::class);
        $this->loginAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_throws_rate_limit_exception_with_available_seconds(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrongpassword',
            remember: false,
        );

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->loginAction->execute($dto);
            } catch (InvalidCredentialsException) {
                // Expected
            }
        }

        // Act & Assert
        try {
            $this->loginAction->execute($dto);
        } catch (TooManyLoginAttemptsException $e) {
            $this->assertGreaterThan(0, $e->getAvailableInSeconds());
        }
    }
}

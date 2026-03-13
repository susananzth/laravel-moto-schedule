<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\ResetPasswordAction;
use App\Modules\Auth\DTOs\ResetPasswordDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ResetPasswordAction $resetPasswordAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPasswordAction = $this->app->make(ResetPasswordAction::class);
    }

    /**
     * @test
     */
    public function it_successfully_resets_password_with_valid_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $newPassword = 'NewSecurePassword123!';

        $dto = new ResetPasswordDTO(
            token: $token,
            email: $user->email,
            password: $newPassword,
        );

        // Act
        $success = $this->resetPasswordAction->execute($dto);

        // Assert
        $this->assertTrue($success);

        // Verify password was hashed correctly
        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * @test
     */
    public function it_returns_false_with_invalid_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $oldPassword = $user->password;
        $invalidToken = 'invalid-token';

        $dto = new ResetPasswordDTO(
            token: $invalidToken,
            email: $user->email,
            password: 'NewSecurePassword123!',
        );

        // Act
        $success = $this->resetPasswordAction->execute($dto);

        // Assert
        $this->assertFalse($success);

        // Verify password was not changed
        $user->refresh();
        $this->assertEquals($oldPassword, $user->password);
    }

    /**
     * @test
     */
    public function it_returns_false_with_wrong_email_for_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $dto = new ResetPasswordDTO(
            token: $token,
            email: 'wrong@example.com',
            password: 'NewSecurePassword123!',
        );

        // Act
        $success = $this->resetPasswordAction->execute($dto);

        // Assert
        $this->assertFalse($success);
    }

    /**
     * @test
     */
    public function it_invalidates_old_tokens_after_reset(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $dto = new ResetPasswordDTO(
            token: $token,
            email: $user->email,
            password: 'NewSecurePassword123!',
        );

        // Act - First reset
        $this->resetPasswordAction->execute($dto);

        // Try to reuse same token
        $dto2 = new ResetPasswordDTO(
            token: $token,
            email: $user->email,
            password: 'AnotherPassword123!',
        );

        $success = $this->resetPasswordAction->execute($dto2);

        // Assert
        $this->assertFalse($success);
    }
}

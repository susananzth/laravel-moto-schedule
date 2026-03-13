<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\ForgotPasswordAction;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use App\Modules\Auth\Exceptions\PasswordResetLinkException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

final class ForgotPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ForgotPasswordAction $forgotPasswordAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forgotPasswordAction = $this->app->make(ForgotPasswordAction::class);
    }

    /**
     * @test
     */
    public function it_sends_password_reset_link_for_existing_user(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'test@example.com']);
        $dto = new ForgotPasswordDTO(email: $user->email);

        // Act & Assert - Should not throw exception
        try {
            $this->forgotPasswordAction->execute($dto);
        } catch (PasswordResetLinkException) {
            $this->fail('Should not throw exception for existing user');
        }
    }

    /**
     * @test
     */
    public function it_throws_exception_for_non_existent_email(): void
    {
        // Arrange
        $dto = new ForgotPasswordDTO(email: 'nonexistent@example.com');

        // Act & Assert
        $this->expectException(PasswordResetLinkException::class);
        $this->forgotPasswordAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_handles_email_case_insensitively(): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'test@example.com']);
        $dto = new ForgotPasswordDTO(email: 'TEST@EXAMPLE.COM');

        // Act & Assert - Should not throw exception
        try {
            $this->forgotPasswordAction->execute($dto);
        } catch (PasswordResetLinkException) {
            $this->fail('Should handle email case-insensitively');
        }
    }
}

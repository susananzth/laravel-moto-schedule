<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\DTOs\LogoutDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogoutActionTest extends TestCase
{
    use RefreshDatabase;

    private LogoutAction $logoutAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logoutAction = $this->app->make(LogoutAction::class);
    }

    /**
     * @test
     */
    public function it_logs_out_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertAuthenticatedAs($user);

        // Act
        $dto = new LogoutDTO(userId: $user->id);
        $this->logoutAction->execute($dto);

        // Assert
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function it_regenerates_csrf_token_on_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $oldToken = csrf_token();

        // Act
        $dto = new LogoutDTO(userId: $user->id);
        $this->logoutAction->execute($dto);

        $newToken = csrf_token();

        // Assert
        $this->assertNotEquals($oldToken, $newToken);
    }

    /**
     * @test
     */
    public function it_invalidates_session(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        session()->put('test_key', 'test_value');
        $this->assertEquals('test_value', session()->get('test_key'));

        // Act
        $dto = new LogoutDTO(userId: $user->id);
        $this->logoutAction->execute($dto);

        // Assert
        $this->assertNull(session()->get('test_key'));
        $this->assertGuest();
    }
}

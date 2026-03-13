<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $userPolicy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPolicy = new UserPolicy();
    }

    /**
     * @test
     */
    public function it_allows_user_to_update_own_profile(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->updateProfile($user, $user));
    }

    /**
     * @test
     */
    public function it_prevents_user_from_updating_other_profile(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->assignRole('Cliente');

        // Act & Assert
        $this->assertFalse($this->userPolicy->updateProfile($user, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_admin_to_update_any_profile(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $otherUser = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->updateProfile($admin, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_user_to_change_own_password(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->changePassword($user, $user));
    }

    /**
     * @test
     */
    public function it_prevents_user_from_changing_other_password(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->assignRole('Cliente');

        // Act & Assert
        $this->assertFalse($this->userPolicy->changePassword($user, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_admin_to_change_any_password(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $otherUser = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->changePassword($admin, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_user_to_view_own_profile(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->view($user, $user));
    }

    /**
     * @test
     */
    public function it_allows_admin_to_view_any_profile(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $otherUser = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->view($admin, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_user_to_delete_own_account(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->deleteAccount($user, $user));
    }

    /**
     * @test
     */
    public function it_prevents_user_from_deleting_other_account(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->assignRole('Cliente');

        // Act & Assert
        $this->assertFalse($this->userPolicy->deleteAccount($user, $otherUser));
    }

    /**
     * @test
     */
    public function it_allows_admin_to_delete_any_account(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $otherUser = User::factory()->create();

        // Act & Assert
        $this->assertTrue($this->userPolicy->deleteAccount($admin, $otherUser));
    }
}

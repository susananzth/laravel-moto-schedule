<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Exceptions\UserAlreadyExistsException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class RegisterActionTest extends TestCase
{
    use RefreshDatabase;

    private RegisterAction $registerAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerAction = $this->app->make(RegisterAction::class);

        // make sure roles used by the action exist in the permission table
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cliente']);
    }

    /**
     * @test
     */
    public function it_successfully_registers_a_new_user(): void
    {
        // Arrange
        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'juan@example.com',
            password: 'SecurePassword123!',
        );

        // Act
        $user = $this->registerAction->execute($dto);

        // Assert
        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'username' => 'juanperez',
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
        ]);

        $this->assertTrue($user->hasRole('Cliente'));
    }

    /**
     * @test
     */
    public function it_hashes_password_on_registration(): void
    {
        // Arrange
        $plainPassword = 'SecurePassword123!';
        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'juan@example.com',
            password: $plainPassword,
        );

        // Act
        $user = $this->registerAction->execute($dto);

        // Assert
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($plainPassword, $user->password));
        $this->assertNotEquals($plainPassword, $user->password);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_email_already_exists(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'existing@example.com',
            password: 'SecurePassword123!',
        );

        // Act & Assert
        $this->expectException(UserAlreadyExistsException::class);
        $this->registerAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_username_already_exists(): void
    {
        // Arrange
        User::factory()->create(['username' => 'juanperez']);

        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'juan@example.com',
            password: 'SecurePassword123!',
        );

        // Act & Assert
        $this->expectException(UserAlreadyExistsException::class);
        $this->registerAction->execute($dto);
    }

    /**
     * @test
     */
    public function it_assigns_cliente_role_to_new_user(): void
    {
        // Arrange
        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'juan@example.com',
            password: 'SecurePassword123!',
        );

        // Act
        $user = $this->registerAction->execute($dto);

        // Assert
        $this->assertTrue($user->hasRole('Cliente'));
    }

    /**
     * @test
     */
    public function it_converts_email_to_lowercase(): void
    {
        // Arrange
        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'Juan@EXAMPLE.COM',
            password: 'SecurePassword123!',
        );

        // Act
        $user = $this->registerAction->execute($dto);

        // Assert
        $this->assertEquals('juan@example.com', $user->email);
    }

    /**
     * @test
     */
    public function it_returns_created_user(): void
    {
        // Arrange
        $dto = new RegisterDTO(
            firstname: 'Juan',
            lastname: 'Pérez',
            username: 'juanperez',
            phone: '999111555',
            email: 'juan@example.com',
            password: 'SecurePassword123!',
        );

        // Act
        $user = $this->registerAction->execute($dto);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->exists);
        $this->assertEquals('juan@example.com', $user->email);
    }
}

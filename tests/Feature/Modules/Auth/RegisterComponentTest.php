<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class RegisterComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_register_form(): void
    {
        Livewire::test(Register::class)
            ->assertStatus(200);
    }

    public function test_it_validates_required_fields(): void
    {
        Livewire::test(Register::class)
            ->set('firstname', '')
            ->set('lastname', '')
            ->set('username', '')
            ->set('phone', '')
            ->set('email', '')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('register')
            ->assertHasErrors([
                'firstname',
                'lastname',
                'username',
                'phone',
                'email',
                'password',
            ]);
    }

    public function test_it_validates_email_format(): void
    {
        Livewire::test(Register::class)
            ->set('firstname', 'Juan')
            ->set('lastname', 'Pérez')
            ->set('username', 'juanperez')
            ->set('phone', '999111555')
            ->set('email', 'not-an-email')
            ->set('password', 'SecurePassword123!')
            ->set('password_confirmation', 'SecurePassword123!')
            ->call('register')
            ->assertHasErrors('email');
    }

    public function test_it_validates_password_confirmation(): void
    {
        Livewire::test(Register::class)
            ->set('firstname', 'Juan')
            ->set('lastname', 'Pérez')
            ->set('username', 'juanperez')
            ->set('phone', '999111555')
            ->set('email', 'juan@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('password_confirmation', 'different')
            ->call('register')
            ->assertHasErrors('password');
    }

    public function test_it_registers_new_user_successfully(): void
    {
        $password = 'SecurePassword123!';

        Livewire::test(Register::class)
            ->set('firstname', 'Juan')
            ->set('lastname', 'Pérez')
            ->set('username', 'juanperez')
            ->set('phone', '999111555')
            ->set('email', 'juan@example.com')
            ->set('password', $password)
            ->set('password_confirmation', $password)
            ->call('register')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'username' => 'juanperez',
        ]);
    }

    public function test_it_prevents_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(Register::class)
            ->set('firstname', 'Juan')
            ->set('lastname', 'Pérez')
            ->set('username', 'juanperez')
            ->set('phone', '999111555')
            ->set('email', 'existing@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('password_confirmation', 'SecurePassword123!')
            ->call('register')
            ->assertHasErrors('email');
    }

    public function test_it_prevents_duplicate_username(): void
    {
        User::factory()->create(['username' => 'juanperez']);

        Livewire::test(Register::class)
            ->set('firstname', 'Juan')
            ->set('lastname', 'Pérez')
            ->set('username', 'juanperez')
            ->set('phone', '999111555')
            ->set('email', 'juan@example.com')
            ->set('password', 'SecurePassword123!')
            ->set('password_confirmation', 'SecurePassword123!')
            ->call('register')
            ->assertHasErrors('username');
    }
}

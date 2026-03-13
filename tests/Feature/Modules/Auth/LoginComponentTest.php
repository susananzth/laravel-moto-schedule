<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class LoginComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_login_form(): void
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }

    public function test_it_validates_required_fields(): void
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email', 'password']);
    }

    public function test_it_validates_email_format(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'not-an-email')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('email');
    }

    public function test_it_logs_in_user_with_valid_credentials(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', $password)
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_it_shows_error_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors('email');
    }

    public function test_it_respects_remember_me_checkbox(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', $password)
            ->set('remember', true)
            ->call('login')
            ->assertRedirect(route('dashboard'));
    }
}

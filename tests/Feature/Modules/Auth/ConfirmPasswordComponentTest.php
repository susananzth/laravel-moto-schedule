<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ConfirmPasswordComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ConfirmPassword::class)
            ->set('password', 'password')
            ->call('confirmPassword')
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertHasNoErrors();
    }

    public function test_invalid_password_shows_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ConfirmPassword::class)
            ->set('password', 'wrong-password')
            ->call('confirmPassword')
            ->assertHasErrors(['password']);
    }
}

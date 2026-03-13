<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Livewire\Livewire;
use Tests\TestCase;

final class PasswordResetRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $this->get('/forgot-password')->assertStatus(200);
    }

    public function test_reset_password_page_accepts_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->get("/reset-password/{$notification->token}?email={$user->email}")
                ->assertStatus(200);
            return true;
        });
    }
}

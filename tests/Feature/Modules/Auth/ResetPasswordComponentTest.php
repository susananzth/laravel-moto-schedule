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

final class ResetPasswordComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_resets_password_with_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = Livewire::test(ResetPassword::class, ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'NewSecurePassword123!')
                ->set('password_confirmation', 'NewSecurePassword123!')
                ->call('resetPassword');

            $response->assertRedirect(route('login', absolute: false));
            return true;
        });
    }
}

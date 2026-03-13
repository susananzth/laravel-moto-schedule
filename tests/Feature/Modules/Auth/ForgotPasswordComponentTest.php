<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Livewire\Livewire;
use Tests\TestCase;

final class ForgotPasswordComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_password_reset_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }
}

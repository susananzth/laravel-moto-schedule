<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PasswordConfirmationRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_page_loads_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/confirm-password');
        $response->assertStatus(200);
    }
}

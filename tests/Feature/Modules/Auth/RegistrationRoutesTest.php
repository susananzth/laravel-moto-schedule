<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegistrationRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible_to_guests(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_registration_page_redirects_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/register')->assertRedirect('/dashboard');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // some views reference a permission that may not be seeded in tests
        // create it so dashboard loads without throwing an exception
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'appointments.be_assigned',
            'guard_name' => 'web',
        ]);
    }

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_login_page_redirects_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/login')->assertRedirect('/dashboard');
    }

    public function test_logout_route_logs_out_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_dashboard_route_redirects_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_route_is_accessible_to_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/dashboard')->assertStatus(200);
    }
}

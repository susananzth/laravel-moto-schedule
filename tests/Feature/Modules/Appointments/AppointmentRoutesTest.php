<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Appointments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppointmentRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_appointment_route_is_protected(): void
    {
        $this->get('/appointments/create')->assertRedirect('/login');
    }

    public function test_verified_user_can_access_create_appointment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get('/appointments/create')->assertStatus(200);
    }
}

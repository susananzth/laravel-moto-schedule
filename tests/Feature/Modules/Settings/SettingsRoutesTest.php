<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SettingsRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_pages_are_protected(): void
    {
        $this->get('/settings/password')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_password_page(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/settings/password')->assertStatus(200);
    }
}

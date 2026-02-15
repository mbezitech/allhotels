<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_settings_page()
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAs($superAdmin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System Settings');
    }

    public function test_non_super_admin_cannot_access_settings_page()
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_settings()
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        
        // Setting is already seeded by migration

        $response = $this->actingAs($superAdmin)->post(route('settings.store'), [
            'booking_expiration_minutes' => '20'
        ]);

        $response->assertRedirect(route('settings.index'));
        $this->assertEquals('20', Setting::get('booking_expiration_minutes'));
    }

    public function test_setting_get_method_returns_default_value()
    {
        $this->assertEquals(15, Setting::get('non_existent_key', 15));
    }
}

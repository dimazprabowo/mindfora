<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Floating text-size control (accessibility for older users) on every layout. */
class FontScaleControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_font_scale_control(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('font-scale-fab', false)
            ->assertSee(app_name(), false);
    }

    public function test_login_page_renders_font_scale_control(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('font-scale-fab', false)
            ->assertSee(__('common.font_size'));
    }

    public function test_app_layout_renders_font_scale_control_with_all_levels(): void
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        $this->actingAs(User::factory()->create()->assignRole('user'));

        $response = $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('font-scale-fab', false)
            ->assertSee(__('common.font_size_increase'));

        foreach (__('common.font_size_levels') as $label) {
            $response->assertSee($label);
        }
    }
}

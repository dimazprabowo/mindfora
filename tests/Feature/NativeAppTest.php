<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Behaviour specific to the Mindfora Android app (User-Agent contains "MindforaApp/"). */
class NativeAppTest extends TestCase
{
    use RefreshDatabase;

    private const NATIVE_UA = 'Mozilla/5.0 (Linux; Android 15) AppleWebKit/537.36 Chrome/150.0 Mobile Safari/537.36 MindforaApp/1.0.0';

    private function native(): static
    {
        return $this->withHeader('User-Agent', self::NATIVE_UA);
    }

    private function participant(): User
    {
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        return User::factory()->create()->assignRole('user');
    }

    public function test_browser_still_gets_the_landing_page(): void
    {
        $this->get('/')->assertOk()->assertViewIs('welcome');
    }

    public function test_app_guest_opening_root_goes_straight_to_login(): void
    {
        $this->native()->get('/')->assertRedirect(route('login'));
    }

    public function test_app_user_opening_root_goes_straight_to_dashboard(): void
    {
        $this->actingAs($this->participant());

        $this->native()->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_app_login_page_is_marked_native_and_hides_home_button(): void
    {
        $this->native()->get(route('login'))
            ->assertOk()
            ->assertSee('class="native-app"', false)
            ->assertSee('viewport-fit=cover', false)
            ->assertDontSee(__('auth.back_to_home'));
    }

    public function test_bottom_navigation_only_renders_inside_the_app(): void
    {
        $this->actingAs($this->participant());

        $this->native()->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('menu.main_navigation'))
            ->assertSee(__('menu.more'));

        $this->flushHeaders()->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(__('menu.main_navigation'));
    }
}

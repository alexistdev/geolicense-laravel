<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@test.com']);

        $this->post('/login', ['email' => 'admin@test.com', 'password' => '1234'])
            ->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_login_redirects_to_user_dashboard(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $this->post('/login', ['email' => 'user@test.com', 'password' => '1234'])
            ->assertRedirect('/user/dashboard');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $this->from('/login')
            ->post('/login', ['email' => 'user@test.com', 'password' => 'wrong'])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_register_creates_a_user_account(): void
    {
        $this->post('/register', [
            'full_name' => 'New Person',
            'email' => 'new@test.com',
            'password' => '1234',
            'password_confirmation' => '1234',
        ])->assertRedirect('/user/dashboard');

        $this->assertDatabaseHas('glo_users', ['email' => 'new@test.com', 'role' => Role::USER->value]);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create(['email' => 'banned@test.com', 'is_suspended' => true]);

        $this->from('/login')
            ->post('/login', ['email' => 'banned@test.com', 'password' => '1234'])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_admin_area_is_forbidden_for_regular_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }
}

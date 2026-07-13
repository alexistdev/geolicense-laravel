<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_writes_a_log_entry(): void
    {
        $user = User::factory()->create(['email' => 'member@test.com', 'full_name' => 'Member One']);

        $this->post('/login', ['email' => 'member@test.com', 'password' => '1234'])
            ->assertRedirect('/user/dashboard');

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'User Login',
            'level' => 'INFO',
            'causer' => 'member@test.com',
            'description' => 'Member One (member@test.com) logged in.',
        ]);
    }

    public function test_failed_login_does_not_write_a_log_entry(): void
    {
        User::factory()->create(['email' => 'member@test.com']);

        $this->from('/login')
            ->post('/login', ['email' => 'member@test.com', 'password' => 'wrong'])
            ->assertRedirect('/login');

        $this->assertDatabaseMissing('glo_logs', ['action' => 'User Login']);
    }

    public function test_logout_writes_a_log_entry_for_the_departing_user(): void
    {
        $user = User::factory()->create(['email' => 'member@test.com', 'full_name' => 'Member One']);

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'User Logout',
            'level' => 'INFO',
            'causer' => 'member@test.com',
            'description' => 'Member One (member@test.com) logged out.',
        ]);
    }
}

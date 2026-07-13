<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_writes_a_log_entry_attributed_to_the_new_user(): void
    {
        $this->post('/register', [
            'full_name' => 'New Person',
            'email' => 'new@test.com',
            'password' => '1234',
            'password_confirmation' => '1234',
        ])->assertRedirect('/user/dashboard');

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'User Registered',
            'level' => 'INFO',
            'causer' => 'new@test.com',
            'description' => 'New account registered: New Person (new@test.com).',
        ]);
    }
}

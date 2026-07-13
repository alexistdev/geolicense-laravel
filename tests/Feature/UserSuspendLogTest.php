<?php

namespace Tests\Feature;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSuspendLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspending_a_user_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create(['full_name' => 'Target User', 'email' => 'target@test.com']);

        $this->actingAs($admin)->patch("/admin/users/{$target->id}/suspend");

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'User Suspended',
            'level' => 'WARNING',
            'causer' => $admin->email,
            'description' => 'Target User (target@test.com) was suspended.',
        ]);
    }

    public function test_reactivating_a_user_writes_an_info_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create(['full_name' => 'Target User', 'email' => 'target@test.com', 'is_suspended' => true]);

        $this->actingAs($admin)->patch("/admin/users/{$target->id}/suspend");

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'User Reactivated',
            'level' => 'INFO',
            'description' => 'Target User (target@test.com) was reactivated.',
        ]);
    }

    public function test_admin_cannot_suspend_own_account_and_nothing_is_logged(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}/suspend");

        $this->assertFalse($admin->fresh()->is_suspended);
        $this->assertSame(0, SystemLog::count());
    }
}

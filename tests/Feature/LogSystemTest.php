<?php

namespace Tests\Feature;

use App\Models\SystemLog;
use App\Models\User;
use Database\Seeders\SystemMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_empty_state_when_there_are_no_logs(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/logs')
            ->assertOk()
            ->assertSee('Log System')
            ->assertSee('No log entries recorded yet.');
    }

    public function test_regular_users_cannot_access_the_log_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/logs')->assertForbidden();
    }

    public function test_logs_are_capped_at_500_and_paginated(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (range(1, 520) as $i) {
            SystemLog::create([
                'level' => 'INFO',
                'description' => "Log entry #{$i}",
            ]);
        }

        // 500 latest, 25 per page => 20 pages.
        $this->actingAs($admin)
            ->get('/admin/logs')
            ->assertOk()
            ->assertSee('of 500')
            ->assertSee('1 / 20');
    }

    public function test_level_filter_narrows_the_result_set(): void
    {
        $admin = User::factory()->admin()->create();
        SystemLog::create(['level' => 'INFO', 'description' => 'an info line']);
        SystemLog::create(['level' => 'ERROR', 'description' => 'a boom happened']);

        $this->actingAs($admin)
            ->get('/admin/logs?level=ERROR')
            ->assertOk()
            ->assertSee('a boom happened')
            ->assertDontSee('an info line');
    }

    public function test_record_helper_appends_an_entry_with_the_current_actor(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        SystemLog::record('Something noteworthy happened', 'WARNING', ['action' => 'Test Event']);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Test Event',
            'level' => 'WARNING',
            'description' => 'Something noteworthy happened',
            'causer' => $admin->email,
        ]);
    }

    public function test_system_menu_seeder_registers_the_admin_menu(): void
    {
        $this->seed(SystemMenuSeeder::class);

        $this->assertDatabaseHas('glo_menus', ['code' => 'ad4', 'name' => 'System', 'urlink' => '#']);
        $this->assertDatabaseHas('glo_menus', ['code' => 'sy1', 'name' => 'Log System', 'urlink' => '/admin/logs']);

        // Re-running must not duplicate rows.
        $this->seed(SystemMenuSeeder::class);
        $this->assertSame(1, \App\Models\Menu::where('code', 'sy1')->count());
        $this->assertDatabaseHas('glo_role_menus', ['role_id' => 'ADMIN']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\LicenseType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTypeLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_license_type_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/license_types', [
            'name' => 'Enterprise',
        ]);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Type Created',
            'level' => 'INFO',
            'causer' => $admin->email,
            'description' => "License type 'Enterprise' was created.",
        ]);
    }

    public function test_updating_a_license_type_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $type = LicenseType::create(['name' => 'Old Tier', 'is_trial' => false]);

        $this->actingAs($admin)->put("/admin/license_types/{$type->id}", [
            'name' => 'New Tier',
        ]);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Type Updated',
            'level' => 'INFO',
            'description' => "License type 'New Tier' was updated.",
        ]);
    }

    public function test_deleting_a_license_type_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $type = LicenseType::create(['name' => 'Doomed Tier', 'is_trial' => false]);

        $this->actingAs($admin)->delete("/admin/license_types/{$type->id}");

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Type Deleted',
            'level' => 'WARNING',
            'description' => "License type 'Doomed Tier' was deleted.",
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Database\Seeder;

/**
 * Adds the Master Data > License Plans entry to the DB-driven sidebar.
 *
 * Kept separate from DatabaseSeeder::seedMenus() (which is all-or-nothing and
 * only runs against an empty menu table) so it can be applied idempotently to
 * an already-seeded database:
 *
 *     php artisan db:seed --class=Database\\Seeders\\LicensePlanMenuSeeder
 */
class LicensePlanMenuSeeder extends Seeder
{
    private const SYSTEM = 'System';

    public function run(): void
    {
        // Parent: Master Data (already present in seeded DBs; created here for safety).
        $masterData = Menu::firstOrCreate(
            ['code' => 'ad2'],
            [
                'name' => 'Master Data',
                'urlink' => '#',
                'classlink' => 'menu-title d-flex align-items-center',
                'icon' => 'bx bx-book-alt',
                'sort_order' => 2,
                'parent_id' => null,
                'type_menu' => 1,
                'created_by' => self::SYSTEM,
                'modified_by' => self::SYSTEM,
            ],
        );

        // Child: License Plans
        $licensePlans = Menu::firstOrCreate(
            ['code' => 'dm6'],
            [
                'name' => 'License Plans',
                'urlink' => '/admin/license_plans',
                'classlink' => 'menu-title d-flex align-items-center',
                'icon' => 'bx bx-package',
                'sort_order' => 3,
                'parent_id' => $masterData->id,
                'type_menu' => 2,
                'created_by' => self::SYSTEM,
                'modified_by' => self::SYSTEM,
            ],
        );

        // Grant both to the ADMIN role.
        foreach ([$masterData, $licensePlans] as $menu) {
            RoleMenu::firstOrCreate(
                ['role_id' => Role::ADMIN->value, 'menu_uuid' => $menu->id],
                ['created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Database\Seeder;

/**
 * Adds the System > Log System entry to the DB-driven sidebar.
 *
 * Kept separate from DatabaseSeeder::seedMenus() (which is all-or-nothing and
 * only runs against an empty menu table) so it can be applied idempotently to
 * an already-seeded database:
 *
 *     php artisan db:seed --class=Database\\Seeders\\SystemMenuSeeder
 */
class SystemMenuSeeder extends Seeder
{
    private const SYSTEM = 'System';

    public function run(): void
    {
        // Parent: System
        $system = Menu::firstOrCreate(
            ['code' => 'ad4'],
            [
                'name' => 'System',
                'urlink' => '#',
                'classlink' => 'menu-title d-flex align-items-center',
                'icon' => 'bx bx-cog',
                'sort_order' => 4,
                'parent_id' => null,
                'type_menu' => 1,
                'created_by' => self::SYSTEM,
                'modified_by' => self::SYSTEM,
            ],
        );

        // Child: Log System
        $logSystem = Menu::firstOrCreate(
            ['code' => 'sy1'],
            [
                'name' => 'Log System',
                'urlink' => '/admin/logs',
                'classlink' => 'menu-title d-flex align-items-center',
                'icon' => 'bx bx-list-ul',
                'sort_order' => 1,
                'parent_id' => $system->id,
                'type_menu' => 2,
                'created_by' => self::SYSTEM,
                'modified_by' => self::SYSTEM,
            ],
        );

        // Grant both to the ADMIN role.
        foreach ([$system, $logSystem] as $menu) {
            RoleMenu::firstOrCreate(
                ['role_id' => Role::ADMIN->value, 'menu_uuid' => $menu->id],
                ['created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM],
            );
        }
    }
}

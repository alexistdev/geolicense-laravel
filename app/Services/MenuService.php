<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\RoleMenu;
use Illuminate\Support\Collection;

/**
 * Ports services.MenuService — resolves the DB-driven sidebar per role.
 */
class MenuService
{
    /**
     * All menus assigned to a role, ordered by sort_order.
     *
     * @return Collection<int, Menu>
     */
    public function getMenusByRole(Role $role): Collection
    {
        $menuIds = RoleMenu::query()
            ->where('role_id', $role->value)
            ->pluck('menu_uuid');

        return Menu::query()
            ->whereIn('id', $menuIds)
            ->orderBy('sort_order')
            ->get();
    }

    public function findByCode(string $code): ?Menu
    {
        return Menu::query()->where('code', $code)->first();
    }
}

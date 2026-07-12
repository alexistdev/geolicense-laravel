<?php

namespace App\Providers;

use App\Services\MenuService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Feed the DB-driven sidebar (glo_menus + glo_role_menus) into the layout,
        // grouped into parents + children — ports DashboardLayout.vue's menu logic.
        View::composer('components.app-layout', function ($view) {
            $user = Auth::user();
            $menus = $user
                ? app(MenuService::class)->getMenusByRole($user->role)
                : new Collection;

            $parents = $menus->whereNull('parent_id')->sortBy('sort_order')->values();
            $childrenByParent = $menus->whereNotNull('parent_id')->groupBy('parent_id');

            $view->with([
                'sidebarUser' => $user,
                'parentMenus' => $parents,
                'childMenusByParent' => $childrenByParent,
            ]);
        });
    }
}

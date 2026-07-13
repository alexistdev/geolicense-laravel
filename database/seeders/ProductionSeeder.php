<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Menu;
use App\Models\RoleMenu;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Production-safe seeder: sidebar menus + a single admin account.
 *
 * Unlike DatabaseSeeder (which is meant for local demos and inserts fake
 * users/orders/invoices/licenses with the weak password "1234"), this seeder
 * inserts NO demo data and never hard-codes a password.
 *
 *     php artisan db:seed --class=ProductionSeeder --force
 *
 * The admin credentials are taken from the environment when present, otherwise
 * you are prompted interactively (password input is hidden). Nothing is written
 * to .env and no password is stored in code:
 *
 *     ADMIN_EMAIL=you@example.com ADMIN_PASSWORD='...' \
 *         php artisan db:seed --class=ProductionSeeder --force
 *
 * Idempotent — safe to run repeatedly. Existing menus/admin are left untouched.
 */
class ProductionSeeder extends Seeder
{
    private const SYSTEM = 'System';

    public function run(): void
    {
        $this->seedBaseMenus();

        // Idempotent add-on menus (System > Log System). Any future menu added
        // to deploy.sh as its own idempotent seeder should also be called here.
        $this->call(SystemMenuSeeder::class);

        $this->seedAdminUser();
    }

    /**
     * The base sidebar tree. Mirrors DatabaseSeeder::seedMenus() but keyed by
     * `code` via firstOrCreate so it is idempotent (that one is all-or-nothing).
     * Keep the two in sync when the base menu set changes.
     */
    private function seedBaseMenus(): void
    {
        // Admin parents
        $adminDashboard = $this->menu('Dashboard', '/admin/dashboard', 1, null, 1, 'ad1', 'bx bx-home-alt');
        $adminMaster = $this->menu('Master Data', '#', 2, null, 1, 'ad2', 'bx bx-book-alt');
        $adminBilling = $this->menu('Billing', '#', 3, null, 1, 'ad3', 'bx bx-book-alt');

        // Admin children
        $this->menu('Users', '/admin/users', 2, $adminMaster->id, 2, 'dm1', 'bx bx-server');
        $this->menu('Products', '/admin/products', 2, $adminMaster->id, 2, 'dm3', 'bx bx-server');
        $this->menu('Licenses Type', '/admin/license_types', 2, $adminMaster->id, 2, 'dm4', 'bx bx-server');
        $this->menu('Invoices', '/admin/invoices', 2, $adminBilling->id, 2, 'dm5', 'bx bx-server');

        // User menus
        $this->menu('Dashboard', '/user/dashboard', 1, null, 2, 'us1', 'bx bx-home-alt');
        $this->menu('License', '/user/license', 2, null, 2, 'us2', 'bx bx-collection');
        $this->menu('Invoices', '/user/invoice', 2, null, 2, 'us3', 'bx bx-money');
        $this->menu('Marketplace', '/user/marketplace', 4, null, 2, 'us5', 'bx bx-store');
        $this->menu('Support', '#', 5, null, 2, 'us4', 'bx bx-headphone');

        $roleMenuCodes = [
            Role::ADMIN->value => ['ad1', 'ad2', 'ad3', 'dm1', 'dm3', 'dm4', 'dm5'],
            Role::USER->value => ['us1', 'us2', 'us3', 'us5', 'us4'],
        ];

        foreach ($roleMenuCodes as $role => $codes) {
            foreach ($codes as $code) {
                $menu = Menu::where('code', $code)->first();
                if ($menu) {
                    RoleMenu::firstOrCreate(
                        ['role_id' => $role, 'menu_uuid' => $menu->id],
                        ['created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM],
                    );
                }
            }
        }
    }

    private function menu(string $name, string $urlink, int $sort, ?string $parentId, int $type, string $code, string $icon): Menu
    {
        return Menu::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'urlink' => $urlink,
                'classlink' => 'menu-title d-flex align-items-center',
                'icon' => $icon,
                'sort_order' => $sort,
                'parent_id' => $parentId,
                'type_menu' => $type,
                'created_by' => self::SYSTEM,
                'modified_by' => self::SYSTEM,
            ],
        );
    }

    private function seedAdminUser(): void
    {
        $email = getenv('ADMIN_EMAIL') ?: $this->command->ask('Admin email');

        if (blank($email)) {
            $this->command->warn('ProductionSeeder: admin email kosong — pembuatan admin dilewati.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command->info("ProductionSeeder: admin '{$email}' sudah ada — dilewati.");

            return;
        }

        $name = getenv('ADMIN_NAME') ?: ($this->command->ask('Admin full name', 'Administrator') ?? 'Administrator');
        $password = getenv('ADMIN_PASSWORD') ?: $this->command->secret('Admin password (input disembunyikan)');

        if (blank($password)) {
            $this->command->warn('ProductionSeeder: password kosong — pembuatan admin dilewati.');

            return;
        }

        User::create([
            'full_name' => $name,
            'email' => $email,
            'password' => $password, // di-hash otomatis oleh cast 'hashed' di model User
            'role' => Role::ADMIN,
            'created_by' => self::SYSTEM,
            'modified_by' => self::SYSTEM,
        ]);

        $this->command->info("ProductionSeeder: admin '{$email}' berhasil dibuat.");
    }
}

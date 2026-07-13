<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\LicenseStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\License;
use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\RoleMenu;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Database\Seeder;

/**
 * Ports config/DatabaseSeeder.java — demo users, catalogue, a completed order
 * flow (+ a pending one to demo payment), and the DB-driven menu tree.
 */
class DatabaseSeeder extends Seeder
{
    private const SYSTEM = 'System';

    public function run(): void
    {
        $this->seedUsers();
        $licenseType = $this->seedLicenseType();
        $product = $this->seedProduct();
        [$monthly, $yearly] = $this->seedLicensePlans($product, $licenseType);
        $this->seedPaymentMethods();
        $this->seedOrderFlow($monthly, $yearly, $product);
        $this->seedMenus();

        // Idempotent — safe to run against a freshly seeded or existing menu tree.
        $this->call(SystemMenuSeeder::class);
    }

    private function seedUsers(): void
    {
        $users = [
            ['alexistdev@gmail.com', 'Alexsander Hendra Wijaya', Role::ADMIN],
            ['user@gmail.com', 'Demo User', Role::USER],
        ];

        foreach ($users as [$email, $name, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                ['full_name' => $name, 'password' => '1234', 'role' => $role, 'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM],
            );
        }
    }

    private function seedLicenseType(): LicenseType
    {
        return LicenseType::firstOrCreate(
            ['name' => 'Premium License'],
            ['description' => 'Premium Version Description', 'is_trial' => false],
        );
    }

    private function seedProduct(): Product
    {
        return Product::firstOrCreate(
            ['sku' => 'SKU-1'],
            [
                'name' => 'GeoBill License Premium',
                'version' => '1.0',
                'description' => 'Geobill is a Software Billing System, running on Spring Boot and Angular.',
                'is_active' => true,
            ],
        );
    }

    /** @return array{0: LicensePlan, 1: LicensePlan} */
    private function seedLicensePlans(Product $product, LicenseType $type): array
    {
        $monthly = LicensePlan::firstOrCreate(
            ['name' => 'Monthly Premium', 'product_id' => $product->id],
            [
                'license_type_id' => $type->id, 'billing_cycle' => 'MONTHLY', 'duration_days' => 30,
                'max_seats' => 5, 'price' => 100000, 'currency' => 'IDR', 'is_active' => true,
            ],
        );

        $yearly = LicensePlan::firstOrCreate(
            ['name' => 'Yearly Premium', 'product_id' => $product->id],
            [
                'license_type_id' => $type->id, 'billing_cycle' => 'YEARLY', 'duration_days' => 365,
                'max_seats' => 5, 'price' => 1000000, 'currency' => 'IDR', 'is_active' => true,
            ],
        );

        return [$monthly, $yearly];
    }

    private function seedPaymentMethods(): void
    {
        $method = PaymentMethod::firstOrCreate(
            ['type' => PaymentMethodType::BANK_TRANSFER],
            ['display_name' => 'Bank Transfer', 'is_active' => true, 'sort_order' => 1],
        );

        BankAccount::firstOrCreate(
            ['payment_method_id' => $method->id, 'account_number' => '1234567890'],
            [
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_holder' => 'PT GeoLicense Indonesia',
                'is_main' => true,
                'is_active' => true,
            ],
        );
    }

    private function seedOrderFlow(LicensePlan $monthly, LicensePlan $yearly, Product $product): void
    {
        $user = User::where('email', 'user@gmail.com')->first();
        if (! $user || Order::where('order_number', 'ORDER-0001')->exists()) {
            return;
        }

        // --- Completed flow: Order -> Item -> Payment(VERIFIED) -> Invoice(PAID) -> License(ACTIVE) ---
        $order = Order::create([
            'user_id' => $user->id, 'order_number' => 'ORDER-0001',
            'currency' => 'IDR', 'status' => OrderStatus::COMPLETED,
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id, 'license_plan_id' => $monthly->id, 'quantity' => 1,
            'unit_price' => $monthly->price, 'total_price' => $monthly->price,
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        Payment::create([
            'order_id' => $order->id, 'provider' => 'MANUAL', 'provider_reference' => 'PAY-0001',
            'amount' => $monthly->price, 'currency' => 'IDR', 'status' => PaymentStatus::VERIFIED,
            'paid_at' => now(), 'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        $code = random_int(100, 999);
        Invoice::create([
            'order_id' => $order->id, 'invoice_number' => 'INV-0001', 'amount' => $monthly->price,
            'currency' => 'IDR', 'status' => InvoiceStatus::PAID, 'issued_at' => now(),
            'unique_code' => $code, 'total_amount' => bcadd((string) $monthly->price, (string) $code, 4),
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        License::create([
            'user_id' => $user->id, 'product_id' => $product->id, 'license_plan_id' => $monthly->id,
            'order_item_id' => $item->id, 'license_key' => LicenseService::generateLicenseKey(),
            'max_seats' => $monthly->max_seats, 'used_seats' => 0, 'issued_at' => now(),
            'expires_at' => now()->addDays($monthly->duration_days), 'status' => LicenseStatus::ACTIVE,
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        // --- Pending flow: Order(PENDING) -> Item -> Invoice(UNPAID) to demo the payment loop ---
        $pendingOrder = Order::create([
            'user_id' => $user->id, 'order_number' => 'ORDER-0002',
            'currency' => 'IDR', 'status' => OrderStatus::PENDING,
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        OrderItem::create([
            'order_id' => $pendingOrder->id, 'license_plan_id' => $yearly->id, 'quantity' => 1,
            'unit_price' => $yearly->price, 'total_price' => $yearly->price,
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);

        $code2 = random_int(100, 999);
        Invoice::create([
            'order_id' => $pendingOrder->id, 'invoice_number' => 'INV-0002', 'amount' => $yearly->price,
            'currency' => 'IDR', 'status' => InvoiceStatus::UNPAID, 'issued_at' => now(),
            'unique_code' => $code2, 'total_amount' => bcadd((string) $yearly->price, (string) $code2, 4),
            'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
        ]);
    }

    private function seedMenus(): void
    {
        if (Menu::exists()) {
            return;
        }

        // Parent menus
        $adminDashboard = $this->menu('Dashboard', '/admin/dashboard', 1, null, 1, 'ad1', 'bx bx-home-alt');
        $adminMaster = $this->menu('Master Data', '#', 2, null, 1, 'ad2', 'bx bx-book-alt');
        $adminBilling = $this->menu('Billing', '#', 3, null, 1, 'ad3', 'bx bx-book-alt');

        $this->menu('Users', '/admin/users', 2, $adminMaster->id, 2, 'dm1', 'bx bx-server');
        $this->menu('Products', '/admin/products', 2, $adminMaster->id, 2, 'dm3', 'bx bx-server');
        $this->menu('Licenses Type', '/admin/license_types', 2, $adminMaster->id, 2, 'dm4', 'bx bx-server');
        $this->menu('License Plans', '/admin/license_plans', 3, $adminMaster->id, 2, 'dm6', 'bx bx-package');
        $this->menu('Invoices', '/admin/invoices', 2, $adminBilling->id, 2, 'dm5', 'bx bx-server');

        // User menus
        $this->menu('Dashboard', '/user/dashboard', 1, null, 2, 'us1', 'bx bx-home-alt');
        $this->menu('License', '/user/license', 2, null, 2, 'us2', 'bx bx-collection');
        $this->menu('Invoices', '/user/invoice', 2, null, 2, 'us3', 'bx bx-money');
        $this->menu('Marketplace', '/user/marketplace', 4, null, 2, 'us5', 'bx bx-store');
        $this->menu('Support', '#', 5, null, 2, 'us4', 'bx bx-headphone');

        $roleMenuCodes = [
            Role::ADMIN->value => ['ad1', 'ad2', 'ad3', 'dm1', 'dm3', 'dm4', 'dm6', 'dm5'],
            Role::USER->value => ['us1', 'us2', 'us3', 'us5', 'us4'],
        ];

        foreach ($roleMenuCodes as $role => $codes) {
            foreach ($codes as $code) {
                $menu = Menu::where('code', $code)->first();
                if ($menu) {
                    RoleMenu::create([
                        'role_id' => $role, 'menu_uuid' => $menu->id,
                        'created_by' => self::SYSTEM, 'modified_by' => self::SYSTEM,
                    ]);
                }
            }
        }
    }

    private function menu(string $name, string $urlink, int $sort, ?string $parentId, int $type, string $code, string $icon): Menu
    {
        return Menu::create([
            'name' => $name,
            'urlink' => $urlink,
            'classlink' => 'menu-title d-flex align-items-center',
            'icon' => $icon,
            'sort_order' => $sort,
            'parent_id' => $parentId,
            'type_menu' => $type,
            'code' => $code,
            'created_by' => self::SYSTEM,
            'modified_by' => self::SYSTEM,
        ]);
    }
}

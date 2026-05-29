<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductDetailSeeder::class,
            PurchaseOrderSeeder::class,
            InventorySeeder::class,
            WarehouseTransferSeeder::class,
            RewardSeeder::class,
            RewardRuleSeeder::class,
            RewardHistorySeeder::class,
            VideoSeeder::class,
            ProductComboSeeder::class,
            HomeSectionSeeder::class,
            OrderSeeder::class,
            InventoryMovementSeeder::class,
            InventoryAuditSeeder::class,
            CommentSeeder::class,
            ServiceInvoiceSeeder::class,
            RepairTicketSeeder::class,
            ArticleSeeder::class,
            CashbookSeeder::class,
            FlashSaleSeeder::class,
        ]);
    }
}

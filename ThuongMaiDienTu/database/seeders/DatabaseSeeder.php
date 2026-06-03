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
            WarrantySeeder::class,
            RewardSeeder::class,
            RewardRuleSeeder::class,
            RewardHistorySeeder::class,
            WarrantyClaimSeeder::class,
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
            AITestArticleSeeder::class,
            FlashSaleSeeder::class,
            LargeProductSeeder::class,
            WishlistRecentlyViewedSeeder::class,
            InstallmentSeeder::class,
            CashbookSeeder::class,
        ]);
    }
}

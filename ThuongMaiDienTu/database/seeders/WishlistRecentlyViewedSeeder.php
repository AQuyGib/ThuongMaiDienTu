<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\WishlistRecentlyViewed;

class WishlistRecentlyViewedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Lấy ngẫu nhiên từ 3 đến 5 sản phẩm để đưa vào danh sách yêu thích
            $randomProducts = $products->random(min(rand(3, 5), $products->count()));

            foreach ($randomProducts as $product) {
                WishlistRecentlyViewed::firstOrCreate([
                    'user_id' => $user->user_id,
                    'product_id' => $product->product_id,
                    'type' => 'Wishlist',
                ]);
            }
        }
    }
}

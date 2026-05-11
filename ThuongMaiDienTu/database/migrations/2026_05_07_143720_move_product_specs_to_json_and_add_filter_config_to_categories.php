<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột filter_config cho bảng categories
        Schema::table('categories', function (Blueprint $table) {
            $table->json('filter_config')->nullable()->after('slug');
        });

        // 2. Chuyển dữ liệu từ các cột rời rạc sang cột specifications (JSON)
        $products = \Illuminate\Support\Facades\DB::table('products')->get();
        $colsToMove = ['ram', 'rom', 'cpu', 'gpu', 'screen', 'os', 'camera', 'battery', 'sim', 'connection'];
        
        foreach ($products as $product) {
            $specs = is_string($product->specifications) && !empty($product->specifications) ? json_decode($product->specifications, true) : [];
            if (!is_array($specs)) $specs = [];

            foreach ($colsToMove as $col) {
                if (!empty($product->$col)) {
                    $specs[$col] = $product->$col;
                }
            }

            \Illuminate\Support\Facades\DB::table('products')
                ->where('product_id', $product->product_id)
                ->update([
                    'specifications' => json_encode($specs)
                ]);
        }

        // 3. Xóa các cột rời rạc đi
        Schema::table('products', function (Blueprint $table) use ($colsToMove) {
            $table->dropColumn($colsToMove);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Phục hồi lại các cột nếu rollback
        Schema::table('products', function (Blueprint $table) {
            $table->string('ram')->nullable();
            $table->string('rom')->nullable();
            $table->string('cpu')->nullable();
            $table->string('gpu')->nullable();
            $table->string('screen')->nullable();
            $table->string('os')->nullable();
            $table->string('camera')->nullable();
            $table->string('battery')->nullable();
            $table->string('sim')->nullable();
            $table->string('connection')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('filter_config');
        });
    }
};

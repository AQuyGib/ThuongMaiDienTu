<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('cpu_chip', 100)->nullable()->after('ram');
            $table->string('gpu_chip', 100)->nullable()->after('cpu_chip');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['cpu_chip', 'gpu_chip']);
        });
    }
};
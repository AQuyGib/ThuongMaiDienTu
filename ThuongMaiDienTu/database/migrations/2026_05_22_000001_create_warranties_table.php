<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->increments('warranty_id');
            $table->unsignedInteger('item_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('warranty_status', ['active', 'expired', 'paused', 'rejected'])->default('active');
            $table->enum('warranty_type', ['manufacturer', 'extended', 'replacement'])->default('manufacturer');
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('cascade');
            $table->index('end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranties');
    }
};

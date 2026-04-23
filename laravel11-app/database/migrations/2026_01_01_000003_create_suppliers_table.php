<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->increments('supplier_id');
            $table->string('name', 100);
            $table->string('contact_info', 200)->nullable();
        });
    }
    public function down() { Schema::dropIfExists('suppliers'); }
};
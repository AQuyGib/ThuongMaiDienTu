<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->increments('cashbook_id');
            $table->enum('type', ['Income', 'Expense']);
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down() { Schema::dropIfExists('cashbooks'); }
};
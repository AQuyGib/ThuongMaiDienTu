<?php
// database/migrations/2026_05_28_000001_create_inventory_audits_tables.php

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
        Schema::create('inventory_audits', function (Blueprint $table) {
            $table->increments('audit_id');
            $table->string('audit_code', 50)->unique();
            $table->string('warehouse_loc', 100);
            $table->enum('status', ['Draft', 'Completed'])->default('Draft');
            $table->string('notes', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
        });

        Schema::create('inventory_audit_details', function (Blueprint $table) {
            $table->increments('detail_id');
            $table->unsignedInteger('audit_id');
            $table->unsignedInteger('variant_id')->nullable();
            $table->string('imei_serial', 50)->nullable();
            $table->integer('system_qty')->default(0);
            $table->integer('actual_qty')->default(0);
            $table->integer('discrepancy_qty')->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('audit_id')->references('audit_id')->on('inventory_audits')->onDelete('cascade');
            $table->foreign('variant_id')->references('variant_id')->on('product_variants')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_details');
        Schema::dropIfExists('inventory_audits');
    }
};

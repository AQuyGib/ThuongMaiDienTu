<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop if exists to cleanly migrate
        Schema::dropIfExists('activity_logs');

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->string('event', 50); // created, updated, deleted, restored, login, export

            // Causer polymorphic fields (e.g., Admin, User, API, System)
            $table->string('causer_type', 100);
            $table->unsignedInteger('causer_id');
            $table->string('causer_name', 150)->nullable();

            // Subject polymorphic fields (e.g., Product, Order, User, Setting)
            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 100)->nullable();

            // State changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            // Anti-tamper Hash Chain (SHA-256 Hash)
            $table->char('hash_chain', 64);

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
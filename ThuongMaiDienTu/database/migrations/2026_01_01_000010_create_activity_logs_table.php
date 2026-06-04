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
        // Xóa bảng nếu đã tồn tại để tránh xung đột khi chạy lại migration
        Schema::dropIfExists('activity_logs');

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->string('event', 50); // created, updated, deleted, restored, login, export

            // Các trường đa hình của Causer (Người thực hiện: Admin, User, API, System)
            $table->string('causer_type', 100);
            $table->unsignedInteger('causer_id');
            $table->string('causer_name', 150)->nullable();

            // Các trường đa hình của Subject (Thực thể chịu tác động: Product, Order, User, Setting,...)
            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 100)->nullable();

            // Lưu trữ thay đổi trạng thái dữ liệu (trước và sau)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Dữ liệu siêu dữ liệu (Metadata)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            // Chuỗi băm Progressive chống giả mạo (SHA-256 Hash Chain)
            $table->char('hash_chain', 64);

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Hoàn tác các thay đổi của migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
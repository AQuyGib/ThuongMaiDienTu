<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('name');
            $table->string('email', 100)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('email');
            $table->dropColumn('contact_info');
        });
    }
    public function down() {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'address']);
            $table->string('contact_info', 200)->nullable()->after('name');
        });
    }
};

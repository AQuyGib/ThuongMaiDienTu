<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Thêm cột `version` cho Optimistic Locking.
     * Mỗi lần update, version tăng thêm 1.
     * Nếu 2 admin cùng sửa 1 user, người submit sau sẽ bị từ chối
     * vì version không khớp.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('status');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};

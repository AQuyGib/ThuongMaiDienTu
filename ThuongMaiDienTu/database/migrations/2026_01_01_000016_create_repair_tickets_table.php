<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->increments('ticket_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('technician_id')->nullable();
            $table->string('imei_serial', 100);
            $table->text('issue_desc');
            $table->dateTime('schedule_date')->nullable();
            $table->unsignedBigInteger('estimated_cost')->default(0);
            $table->enum('status', ['Received', 'Waiting_Parts', 'Done'])->default('Received');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('technician_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }
    public function down() { Schema::dropIfExists('repair_tickets'); }
};
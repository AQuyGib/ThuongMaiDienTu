<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_room_members');
        Schema::dropIfExists('chat_rooms');

        // 1. Chat Rooms table
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->string('room_id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('group'); // group, announcement, private, ai
            $table->text('pinned_message')->nullable();
            $table->timestamps();
        });

        // 2. Chat Room Members table
        Schema::create('chat_room_members', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->unsignedInteger('user_id');
            $table->string('room_role')->default('member'); // leader, co-leader, member
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // 3. Chat Messages table
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('message_id')->primary();
            $table->string('room_id');
            $table->unsignedInteger('sender_id');
            $table->string('sender_name');
            $table->string('sender_role');
            $table->string('avatar_color');
            $table->text('content')->nullable();
            
            // Reply context
            $table->string('reply_to_sender')->nullable();
            $table->text('reply_to_content')->nullable();
            
            // Attachments
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type')->nullable();
            $table->text('attachment_url')->nullable();
            $table->string('attachment_size')->nullable();
            
            $table->boolean('is_read')->default(false);
            $table->text('reactions')->nullable(); // stored as JSON string or cast in Eloquent
            $table->timestamps();

            $table->foreign('room_id')->references('room_id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('sender_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_room_members');
        Schema::dropIfExists('chat_rooms');
    }
};

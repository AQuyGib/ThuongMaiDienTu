<?php

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
        Schema::create('articles', function (Blueprint $table) {
            $table->id('article_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            
            // Format type
            $table->enum('format_type', ['standard', 'lookbook', 'storytelling'])->default('standard');
            
            // Ecosystem: Repair Ticket
            $table->unsignedInteger('related_ticket_id')->nullable();
            $table->foreign('related_ticket_id')->references('ticket_id')->on('repair_tickets')->nullOnDelete();
            
            // UGC & Gamification
            $table->unsignedInteger('author_id');
            $table->foreign('author_id')->references('user_id')->on('users')->cascadeOnDelete();
            
            $table->enum('author_type', ['admin', 'customer'])->default('admin');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('reward_points_awarded')->default(0);
            
            // Shoppable
            $table->json('embedded_product_ids')->nullable(); 
            
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

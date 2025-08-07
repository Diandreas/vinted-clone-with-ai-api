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
        Schema::create('lives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->string('stream_key', 64)->unique();
            $table->string('stream_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('viewers_count')->default(0);
            $table->integer('max_viewers')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->json('tags')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
            $table->index(['is_featured', 'status']);
            $table->index(['viewers_count', 'status']);
            $table->index('stream_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lives');
    }
};

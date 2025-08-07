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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video', 'text', 'product'])->default('image');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->text('text_overlay')->nullable();
            $table->string('background_color')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamp('expires_at');
            $table->boolean('is_highlighted')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'expires_at']);
            $table->index(['expires_at', 'is_highlighted']);
            $table->index(['type', 'expires_at']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};

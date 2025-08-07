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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_public')->default(true);
            $table->text('seller_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['reviewed_id', 'rating']);
            $table->index(['reviewer_id', 'created_at']);
            $table->index(['is_public', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

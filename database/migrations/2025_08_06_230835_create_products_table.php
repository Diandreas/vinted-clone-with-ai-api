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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('condition_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->enum('status', ['draft', 'active', 'sold', 'reserved', 'removed'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_boosted')->default(false);
            $table->timestamp('boosted_until')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamp('sold_at')->nullable();
            $table->json('tags')->nullable();
            $table->json('measurements')->nullable();
            $table->decimal('shipping_cost', 8, 2)->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->decimal('minimum_offer', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['price', 'status']);
            $table->index(['is_featured', 'status']);
            $table->index(['is_boosted', 'boosted_until']);
            $table->index(['views_count', 'status']);
            $table->index(['likes_count', 'status']);
            // $table->fullText(['title', 'description']); // Not supported by SQLite
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};



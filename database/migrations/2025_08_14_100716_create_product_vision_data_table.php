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
        Schema::create('product_vision_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->json('labels')->nullable();
            $table->json('objects')->nullable();
            $table->json('colors')->nullable();
            $table->json('text')->nullable();
            $table->json('faces')->nullable();
            $table->json('web_entities')->nullable();
            $table->json('similar_images')->nullable();
            $table->decimal('similarity_score', 8, 6)->nullable();
            $table->text('feature_vector')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'processed']);
            $table->index('similarity_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vision_data');
    }
};

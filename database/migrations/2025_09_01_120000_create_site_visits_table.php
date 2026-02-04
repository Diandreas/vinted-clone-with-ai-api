<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visited_on');
            $table->string('ip_hash', 64);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_agent', 255)->nullable();
            $table->string('path', 255)->nullable();
            $table->timestamps();

            $table->unique(['visited_on', 'ip_hash']);
            $table->index('visited_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};

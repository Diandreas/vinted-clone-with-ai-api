<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'conversation_id')) {
                $table->unsignedBigInteger('conversation_id')->after('id');
            }
            if (!Schema::hasColumn('messages', 'sender_id')) {
                $table->unsignedBigInteger('sender_id')->after('conversation_id');
            }
            if (!Schema::hasColumn('messages', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('messages', 'type')) {
                $table->string('type')->default('text')->after('product_id');
            }
            if (!Schema::hasColumn('messages', 'content')) {
                $table->text('content')->after('type');
            }
            if (!Schema::hasColumn('messages', 'attachment_url')) {
                $table->string('attachment_url')->nullable()->after('content');
            }
            if (!Schema::hasColumn('messages', 'attachment_type')) {
                $table->string('attachment_type')->nullable()->after('attachment_url');
            }
            if (!Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('attachment_type');
            }
            if (!Schema::hasColumn('messages', 'metadata')) {
                $table->json('metadata')->nullable()->after('read_at');
            }
            if (!Schema::hasColumn('messages', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'messages_conversation_created_at_index');
            try {
                $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            try { $table->dropForeign(['conversation_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['sender_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['product_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex('messages_conversation_created_at_index'); } catch (\Throwable $e) {}

            foreach ([
                'conversation_id','sender_id','product_id','type','content','attachment_url','attachment_type','read_at','metadata','deleted_at'
            ] as $col) {
                if (Schema::hasColumn('messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};



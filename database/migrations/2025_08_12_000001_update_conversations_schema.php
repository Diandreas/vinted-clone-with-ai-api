<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes d'abord
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('conversations', 'buyer_id')) {
                $table->unsignedBigInteger('buyer_id')->after('product_id');
            }
            if (!Schema::hasColumn('conversations', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->after('buyer_id');
            }
            if (!Schema::hasColumn('conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('seller_id');
            }
            if (!Schema::hasColumn('conversations', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('last_message_at');
            }
            if (!Schema::hasColumn('conversations', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Ajouter les index seulement s'ils n'existent pas
        $this->addIndexIfNotExists('conversations', ['buyer_id', 'seller_id'], 'conversations_buyer_seller_index');
        $this->addIndexIfNotExists('conversations', ['product_id', 'buyer_id', 'seller_id'], 'conversations_product_buyer_seller_index');

        // Ajouter les clés étrangères avec gestion d'erreur
        Schema::table('conversations', function (Blueprint $table) {
            try {
                $table->foreign('product_id', 'conversations_product_id_fk')
                    ->references('id')
                    ->on('products')
                    ->onDelete('set null');
            } catch (\Throwable $e) {
                // Contrainte déjà existante
            }

            try {
                $table->foreign('buyer_id', 'conversations_buyer_id_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {
                // Contrainte déjà existante
            }

            try {
                $table->foreign('seller_id', 'conversations_seller_id_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {
                // Contrainte déjà existante
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Supprimer les clés étrangères avec noms spécifiques
            try {
                $table->dropForeign('conversations_product_id_fk');
            } catch (\Throwable $e) {}

            try {
                $table->dropForeign('conversations_buyer_id_fk');
            } catch (\Throwable $e) {}

            try {
                $table->dropForeign('conversations_seller_id_fk');
            } catch (\Throwable $e) {}

            // Supprimer les index
            $this->dropIndexIfExists('conversations', 'conversations_buyer_seller_index');
            $this->dropIndexIfExists('conversations', 'conversations_product_buyer_seller_index');

            // Supprimer les colonnes si elles existent
            $columnsToRemove = ['product_id', 'buyer_id', 'seller_id', 'last_message_at', 'is_archived', 'deleted_at'];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Ajouter un index s'il n'existe pas
     */
    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            $columnList = '`' . implode('`, `', $columns) . '`';
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnList})");
        }
    }

    /**
     * Supprimer un index s'il existe
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        }
    }

    /**
     * Vérifier si un index existe
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $database = config('database.connections.' . config('database.default') . '.database');

        $result = DB::selectOne("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = ?
            AND table_name = ?
            AND index_name = ?
        ", [$database, $table, $indexName]);

        return $result && $result->count > 0;
    }
};

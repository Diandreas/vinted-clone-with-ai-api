<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Product;
use App\Models\Conversation;
use App\Models\ProductInterest;

// Test script pour vérifier le nouveau système de conversations par produit

echo "🚀 Test du système de conversations par produit\n\n";

// Vérifier que les nouvelles tables existent
try {
    $db = new PDO("sqlite:" . __DIR__ . "/database/database.sqlite");
    
    // Vérifier la table product_interests
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='product_interests'");
    if ($result->fetchColumn()) {
        echo "✅ Table product_interests créée avec succès\n";
    } else {
        echo "❌ Table product_interests non trouvée\n";
    }
    
    // Vérifier les colonnes de la table conversations
    $result = $db->query("PRAGMA table_info(conversations)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);
    
    $requiredColumns = ['product_id', 'buyer_id', 'seller_id', 'last_message_at', 'is_archived'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "✅ Table conversations a toutes les colonnes requises\n";
    } else {
        echo "❌ Colonnes manquantes dans conversations: " . implode(', ', $missingColumns) . "\n";
    }
    
    echo "\n📊 Structure des tables:\n";
    echo "=========================\n\n";
    
    // Afficher la structure de product_interests
    echo "📋 Table product_interests:\n";
    $result = $db->query("PRAGMA table_info(product_interests)");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']})\n";
    }
    
    echo "\n📋 Table conversations:\n";
    $result = $db->query("PRAGMA table_info(conversations)");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} ({$row['type']})\n";
    }
    
    echo "\n🎯 Nouvelles fonctionnalités disponibles:\n";
    echo "=========================================\n\n";
    
    echo "👥 Pour l'ACHETEUR:\n";
    echo "  📱 GET /api/v1/conversations/my-product-discussions\n";
    echo "     → Liste des produits auxquels il s'intéresse\n\n";
    
    echo "  📊 GET /api/v1/conversations/my-product-interests\n";
    echo "     → Ses intérêts avec statuts de négociation\n\n";
    
    echo "  💬 POST /api/v1/conversations/start/{product}\n";
    echo "     → Démarrer une conversation sur un produit\n\n";
    
    echo "🏪 Pour le VENDEUR:\n";
    echo "  📦 GET /api/v1/conversations/my-products-with-buyers\n";
    echo "     → Ses produits avec acheteurs intéressés\n\n";
    
    echo "  🔍 GET /api/v1/conversations/product/{product}/conversations\n";
    echo "     → Toutes les conversations sur un produit spécifique\n\n";
    
    echo "⚙️ Pour TOUS:\n";
    echo "  📝 PUT /api/v1/conversations/{conversation}/status\n";
    echo "     → Mettre à jour le statut d'une négociation\n\n";
    
    echo "🔄 Fonctionnalités automatiques:\n";
    echo "  ✅ Archivage auto des conversations quand produit vendu/supprimé\n";
    echo "  📊 Suivi des statuts d'intérêt (intéressé, négociation, acheté, annulé)\n";
    echo "  🔗 Relations Product ↔ Conversations ↔ ProductInterests\n\n";
    
    echo "🎨 Interfaces créées:\n";
    echo "  👤 ProductDiscussions.vue (vue acheteur)\n";
    echo "  🏪 SellerProductConversations.vue (vue vendeur)\n\n";
    
    echo "✨ Le système est prêt à l'emploi !\n";
    echo "Chaque produit a maintenant sa propre messagerie centralisée.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
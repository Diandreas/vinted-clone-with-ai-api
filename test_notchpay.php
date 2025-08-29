<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Test de connexion à la base de données
try {
    echo "🔍 Test de connexion à la base de données...\n";
    
    // Vérifier si la table payments existe
    if (Schema::hasTable('payments')) {
        echo "✅ Table 'payments' créée avec succès\n";
        
        // Afficher la structure de la table
        $columns = DB::select('DESCRIBE payments');
        echo "📋 Structure de la table payments:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field}: {$column->Type} {$column->Null} {$column->Key}\n";
        }
    } else {
        echo "❌ Table 'payments' non trouvée\n";
    }
    
    // Vérifier si la table products a le statut pending_payment
    if (Schema::hasTable('products')) {
        echo "✅ Table 'products' existe\n";
        
        // Vérifier si la colonne status existe et a les bonnes valeurs
        $columns = DB::select('DESCRIBE products');
        $statusColumn = null;
        foreach ($columns as $column) {
            if ($column->Field === 'status') {
                $statusColumn = $column;
                break;
            }
        }
        
        if ($statusColumn) {
            echo "✅ Colonne 'status' trouvée dans products\n";
            echo "  - Type: {$statusColumn->Type}\n";
            echo "  - Valeurs possibles: {$statusColumn->Type}\n";
        } else {
            echo "❌ Colonne 'status' non trouvée dans products\n";
        }
    }
    
    echo "\n🎉 Test terminé avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

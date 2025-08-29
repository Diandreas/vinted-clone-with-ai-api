<?php

// Test du callback corrigé
echo "🧪 Test du callback NotchPay corrigé\n";
echo "==================================\n\n";

echo "1. Test du callback avec statut 'complete' et référence valide\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/callback?status=complete&reference=prod_26_test123');
    echo "✅ Réponse reçue\n";
    
    // Vérifier si c'est une page de succès ou d'erreur
    if (strpos($response, 'Paiement Réussi') !== false) {
        echo "   🎉 Page de succès affichée\n";
    } elseif (strpos($response, 'Erreur de Paiement') !== false) {
        echo "   ❌ Page d'erreur affichée\n";
    } else {
        echo "   ⚠️ Page neutre affichée\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n2. Test du callback avec statut 'failed'\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/callback?status=failed&reference=prod_26_test456');
    echo "✅ Réponse reçue\n";
    
    if (strpos($response, 'Erreur de Paiement') !== false) {
        echo "   ❌ Page d'erreur affichée (correct)\n";
    } else {
        echo "   ⚠️ Page inattendue affichée\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n3. Test du callback sans référence\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/callback?status=complete');
    echo "✅ Réponse reçue\n";
    
    if (strpos($response, 'Erreur de Paiement') !== false) {
        echo "   ❌ Page d'erreur affichée (correct - pas de référence)\n";
    } else {
        echo "   ⚠️ Page inattendue affichée\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎉 Test terminé !\n";

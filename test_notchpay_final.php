<?php

// Test final de l'API NotchPay
echo "🧪 Test final de l'API NotchPay\n";
echo "==============================\n\n";

$token = "76|N1MtfQg5yBFyT8u1yRA5BgSqZBrosMcF2wd0sP6t78d728c5";

echo "1. Test de l'API NotchPay avec token\n";
try {
    $response = file_get_contents('http://localhost:8000/api/v1/notchpay/initialize', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ],
            'content' => json_encode([
                'product_id' => 26, // ID du produit de test
                'amount' => 500,    // Frais de publication
                'email' => 'test@example.com'
            ])
        ]
    ]));
    
    echo "✅ Réponse reçue: " . $response . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur API NotchPay: " . $e->getMessage() . "\n";
}

echo "\n2. Test de la route de callback publique\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/callback?status=complete&reference=test123');
    echo "✅ Réponse callback: " . $response . "\n";
} catch (Exception $e) {
    echo "❌ Erreur callback: " . $e->getMessage() . "\n";
}

echo "\n3. Test de la page de résultat\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/result');
    echo "✅ Page de résultat accessible\n";
} catch (Exception $e) {
    echo "❌ Erreur page résultat: " . $e->getMessage() . "\n";
}

echo "\n🎉 Test terminé !\n";

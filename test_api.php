<?php

require_once 'vendor/autoload.php';

// Test simple de l'API NotchPay
echo "🧪 Test de l'API NotchPay\n";
echo "========================\n\n";

// Test 1: Vérifier que la route existe
echo "1. Test de la route /api/v1/notchpay/initialize\n";
try {
    $response = file_get_contents('http://localhost:8000/api/v1/notchpay/initialize', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'content' => json_encode([
                'product_id' => 1,
                'amount' => 1000,
                'email' => 'test@example.com'
            ])
        ]
    ]));
    
    echo "✅ Réponse reçue: " . $response . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n2. Test de la route /payment/callback\n";
try {
    $response = file_get_contents('http://localhost:8000/payment/callback?status=complete&reference=test123');
    echo "✅ Réponse reçue: " . $response . "\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎉 Test terminé !\n";

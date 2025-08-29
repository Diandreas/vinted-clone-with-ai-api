<?php

require_once 'vendor/autoload.php';

// Test de l'API NotchPay avec authentification
echo "🧪 Test de l'API NotchPay avec authentification\n";
echo "==============================================\n\n";

// Test 1: Connexion de l'utilisateur
echo "1. Test de connexion de l'utilisateur\n";
try {
    $response = file_get_contents('http://localhost:8000/api/v1/auth/login', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'content' => json_encode([
                'email' => 'test@example.com',
                'password' => 'password'
            ])
        ]
    ]));
    
    $loginData = json_decode($response, true);
    
    if (isset($loginData['user']['token'])) {
        echo "✅ Connexion réussie\n";
        echo "   Token: " . substr($loginData['user']['token'], 0, 20) . "...\n";
        $token = $loginData['user']['token'];
    } else {
        echo "❌ Échec de la connexion: " . $response . "\n";
        exit;
    }
    
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit;
}

echo "\n2. Test de l'API NotchPay avec token\n";
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

echo "\n🎉 Test terminé !\n";

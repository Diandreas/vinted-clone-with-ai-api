<?php

// Test simple pour envoyer un message via l'API

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;

echo "🔧 TEST SIMPLE D'ENVOI DE MESSAGE\n";
echo "=================================\n\n";

// Trouver un utilisateur de test
$user = User::where('email', 'njandjeu@gmail.com')->first();
if (!$user) {
    echo "❌ Utilisateur de test non trouvé\n";
    exit(1);
}

// Trouver un produit qui n'appartient PAS à cet utilisateur
$product = Product::where('user_id', '!=', $user->id)->first();
if (!$product) {
    echo "❌ Aucun produit d'autres utilisateurs trouvé\n";
    exit(1);
}

echo "👤 Utilisateur: {$user->name} (ID: {$user->id})\n";
echo "📦 Produit: {$product->title} (ID: {$product->id})\n";
echo "🏪 Propriétaire: {$product->user->name} (ID: {$product->user_id})\n\n";

// Générer un token pour cet utilisateur
$token = $user->createToken('test-token')->plainTextToken;
echo "🔑 Token généré: " . substr($token, 0, 40) . "...\n\n";

// Maintenant testons l'API avec curl
$url = "http://localhost:8000/api/v1/conversations/start/{$product->id}";
$data = json_encode(['message' => 'Test message depuis PHP']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);

echo "📡 Envoi de la requête...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📊 Code HTTP: {$httpCode}\n";
echo "📄 Réponse:\n{$response}\n\n";

if ($httpCode === 200 || $httpCode === 201) {
    $responseData = json_decode($response, true);
    if ($responseData['success']) {
        echo "✅ MESSAGE ENVOYÉ AVEC SUCCÈS!\n";
        echo "   Conversation ID: {$responseData['data']['id']}\n";
    } else {
        echo "❌ Échec envoi: {$responseData['message']}\n";
    }
} else {
    echo "❌ ERREUR HTTP {$httpCode}\n";
}

echo "\n📋 Instructions pour le frontend:\n";
echo "1. Connectez-vous avec: njandjeu@gmail.com\n";
echo "2. Testez l'envoi de message sur le produit ID {$product->id}\n";
echo "3. Vérifiez la console du navigateur pour les logs\n";
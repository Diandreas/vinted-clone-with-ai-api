<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "🔧 TEST FINAL - ENDPOINT MY-PRODUCT-DISCUSSIONS\n";
echo "==============================================\n\n";

// Utilisateur de test
$user = User::find(7);
if (!$user) {
    echo "❌ Utilisateur 7 non trouvé\n";
    exit(1);
}

// Générer un nouveau token
$token = $user->createToken('test-final')->plainTextToken;
echo "👤 Utilisateur: {$user->name} (ID: {$user->id})\n";
echo "🔑 Nouveau token: " . substr($token, 0, 30) . "...\n\n";

// Test direct de l'endpoint avec curl
$url = 'http://localhost:8000/api/v1/conversations/my-product-discussions';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

echo "📡 Appel GET $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Status HTTP: $httpCode\n";
if ($error) {
    echo "❌ Erreur cURL: $error\n";
}

echo "📄 Réponse:\n";
if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['data'])) {
        echo "✅ SUCCESS! Conversations trouvées: " . count($data['data']) . "\n\n";
        
        if (count($data['data']) > 0) {
            echo "📝 Liste des conversations:\n";
            foreach ($data['data'] as $index => $conv) {
                echo "  " . ($index + 1) . ". {$conv['product']['title']}\n";
                echo "     🔸 Conv ID: {$conv['id']}\n";
                echo "     🔸 Vendeur: {$conv['seller']['name']}\n";
                echo "     🔸 Dernière activité: {$conv['last_message_at']}\n";
                if (isset($conv['last_message'])) {
                    $content = substr($conv['last_message']['content'], 0, 50);
                    echo "     🔸 Dernier message: \"{$content}...\"\n";
                }
                echo "\n";
            }
        } else {
            echo "ℹ️ Aucune conversation trouvée pour cet utilisateur\n";
        }
    } else {
        echo "❌ Réponse invalide\n";
        echo $response . "\n";
    }
} else {
    echo "❌ Erreur HTTP $httpCode\n";
    echo $response . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 RÉSULTAT:\n";
if ($httpCode === 200) {
    echo "✅ L'endpoint fonctionne correctement!\n";
    echo "   Le problème était dans l'ordre des routes Laravel.\n";
    echo "   Maintenant le frontend devrait pouvoir récupérer les conversations.\n";
} else {
    echo "❌ L'endpoint ne fonctionne toujours pas.\n";
    echo "   Vérifiez les logs Laravel pour plus de détails.\n";
}
echo "\n";
?>
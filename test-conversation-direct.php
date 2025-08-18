<?php
/**
 * Test direct de l'API de conversation
 */

echo "🧪 Test direct de l'API de conversation\n";
echo "=====================================\n\n";

// Configuration
$baseUrl = 'http://localhost:8000/api/v1';
$testEmail = 'test@example.com';
$testPassword = 'password123';

// 1. Test de connexion
echo "1️⃣ Test de connexion...\n";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/auth/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($loginData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $loginResult = json_decode($response, true);
    if (isset($loginResult['token'])) {
        $token = $loginResult['token'];
        echo "✅ Connexion réussie\n";
        echo "   Token: " . substr($token, 0, 20) . "...\n";
        echo "   Utilisateur: " . ($loginResult['user']['name'] ?? 'N/A') . "\n\n";
    } else {
        echo "❌ Connexion échouée: Token manquant\n";
        echo "   Réponse: " . $response . "\n\n";
        exit(1);
    }
} else {
    echo "❌ Connexion échouée: HTTP $httpCode\n";
    echo "   Réponse: " . $response . "\n\n";
    exit(1);
}

// 2. Test de la route de conversation AVEC authentification
echo "2️⃣ Test de la route /conversations/start/2 AVEC authentification...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . "/conversations/start/2",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['message' => 'Test message authentifié']),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
echo "   Réponse: " . $response . "\n\n";

if ($httpCode === 200 || $httpCode === 201) {
    echo "✅ Conversation démarrée avec succès\n";
} else {
    echo "❌ Échec démarrage conversation\n";
    
    if ($httpCode === 405) {
        echo "🚨 Erreur 405: Méthode non autorisée\n";
        echo "   Cela indique un problème de route ou de middleware\n\n";
        
        // Test des autres méthodes HTTP
        echo "3️⃣ Test des autres méthodes HTTP...\n";
        $methods = ['GET', 'PUT', 'DELETE', 'PATCH'];
        
        foreach ($methods as $method) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $baseUrl . "/conversations/start/2",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "   $method: HTTP $httpCode\n";
        }
        
        echo "\n4️⃣ Vérification des routes disponibles...\n";
        
        // Test de la route GET /conversations
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . "/conversations",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   GET /conversations: HTTP $httpCode\n";
        
        // Test de la route POST /conversations (sans paramètre)
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . "/conversations",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['message' => 'Test']),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   POST /conversations: HTTP $httpCode\n";
        
    } elseif ($httpCode === 401) {
        echo "🚨 Erreur 401: Non authentifié\n";
        echo "   Vérifiez le token d'authentification\n\n";
    } elseif ($httpCode === 404) {
        echo "🚨 Erreur 404: Route non trouvée\n";
        echo "   Vérifiez que la route est bien définie\n\n";
    } else {
        echo "🚨 Erreur HTTP $httpCode\n";
        echo "   Vérifiez les logs Laravel\n\n";
    }
}

echo "\n🎯 Diagnostic:\n";
echo "=============\n";

if ($httpCode === 405) {
    echo "❌ PROBLÈME CONFIRMÉ: Route /conversations/start/2 ne supporte que GET/HEAD\n";
    echo "💡 Causes possibles:\n";
    echo "   1. Conflit d'ordre des routes (start/{product} vs {conversation})\n";
    echo "   2. Cache des routes non vidé\n";
    echo "   3. Middleware qui bloque POST\n";
    echo "   4. Route mal définie\n\n";
    
    echo "🔧 Solutions à essayer:\n";
    echo "   1. php artisan route:clear\n";
    echo "   2. php artisan config:clear\n";
    echo "   3. php artisan cache:clear\n";
    echo "   4. Vérifier l'ordre des routes dans routes/api.php\n";
    echo "   5. Redémarrer le serveur Laravel\n\n";
} else {
    echo "✅ Route fonctionne correctement\n";
}

echo "\n📝 Pour tester manuellement:\n";
echo "curl -X POST '$baseUrl/conversations/start/2' \\\n";
echo "  -H 'Authorization: Bearer $token' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"message\": \"Test message\"}'\n";
?>


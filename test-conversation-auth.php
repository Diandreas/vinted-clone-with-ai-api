<?php
/**
 * Script de test pour vérifier l'authentification et tester la route de conversation
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/v1';
$testEmail = 'test@example.com';
$testPassword = 'password123';

echo "🔍 Test d'authentification et de conversation\n";
echo "============================================\n\n";

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

// 2. Test de récupération de l'utilisateur
echo "2️⃣ Test de récupération de l'utilisateur...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/auth/user',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $userResult = json_decode($response, true);
    echo "✅ Utilisateur récupéré\n";
    echo "   ID: " . ($userResult['user']['id'] ?? 'N/A') . "\n";
    echo "   Nom: " . ($userResult['user']['name'] ?? 'N/A') . "\n";
    echo "   Email: " . ($userResult['user']['email'] ?? 'N/A') . "\n\n";
} else {
    echo "❌ Échec récupération utilisateur: HTTP $httpCode\n";
    echo "   Réponse: " . $response . "\n\n";
    exit(1);
}

// 3. Test de récupération des produits
echo "3️⃣ Test de récupération des produits...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/products?per_page=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $productsResult = json_decode($response, true);
    if (isset($productsResult['data']) && count($productsResult['data']) > 0) {
        $product = $productsResult['data'][0];
        $productId = $product['id'];
        echo "✅ Produit récupéré\n";
        echo "   ID: $productId\n";
        echo "   Titre: " . ($product['title'] ?? 'N/A') . "\n";
        echo "   Propriétaire: " . ($product['user']['name'] ?? 'N/A') . "\n\n";
    } else {
        echo "❌ Aucun produit trouvé\n";
        exit(1);
    }
} else {
    echo "❌ Échec récupération produits: HTTP $httpCode\n";
    echo "   Réponse: " . $response . "\n\n";
    exit(1);
}

// 4. Test de démarrage de conversation (sans authentification - devrait échouer)
echo "4️⃣ Test de démarrage de conversation SANS authentification...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . "/conversations/start/$productId",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['message' => 'Test message']),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 401) {
    echo "✅ Test réussi: Route protégée (401 Unauthorized)\n\n";
} else {
    echo "❌ Test échoué: Route non protégée (HTTP $httpCode)\n";
    echo "   Réponse: " . $response . "\n\n";
}

// 5. Test de démarrage de conversation AVEC authentification
echo "5️⃣ Test de démarrage de conversation AVEC authentification...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . "/conversations/start/$productId",
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

if ($httpCode === 200 || $httpCode === 201) {
    $conversationResult = json_decode($response, true);
    echo "✅ Conversation démarrée avec succès\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Réponse: " . json_encode($conversationResult, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ Échec démarrage conversation: HTTP $httpCode\n";
    echo "   Réponse: " . $response . "\n\n";
    
    // Afficher les détails de l'erreur
    if ($httpCode === 405) {
        echo "🚨 Erreur 405: Méthode non autorisée\n";
        echo "   Cela peut indiquer un problème de route ou de middleware\n\n";
    }
}

// 6. Test des routes disponibles
echo "6️⃣ Test des routes de conversation disponibles...\n";
$routes = [
    'GET /conversations' => '/conversations',
    'POST /conversations' => '/conversations',
    'GET /conversations/my-product-discussions' => '/conversations/my-product-discussions',
    'POST /conversations/start/{product}' => "/conversations/start/$productId",
    'GET /conversations/product/{product}/conversations' => "/conversations/product/$productId/conversations"
];

foreach ($routes as $route => $url) {
    $method = strpos($route, 'POST') !== false ? 'POST' : 'GET';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => 'Test']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = $httpCode < 400 ? '✅' : '❌';
    echo "   $status $route: HTTP $httpCode\n";
}

echo "\n🎯 Résumé du test\n";
echo "================\n";
echo "• Authentification: " . (isset($token) ? '✅ OK' : '❌ ÉCHEC') . "\n";
echo "• Route conversation: " . ($httpCode === 200 || $httpCode === 201 ? '✅ OK' : '❌ ÉCHEC') . "\n";
echo "• Token valide: " . (isset($token) ? '✅ Oui' : '❌ Non') . "\n";

if (isset($token)) {
    echo "\n🔑 Token pour tests frontend:\n";
    echo "localStorage.setItem('auth_token', '$token');\n";
}
?>

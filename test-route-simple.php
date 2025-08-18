<?php
/**
 * Test simple de la route de conversation
 */

echo "🔍 Test simple de la route /conversations/start/{product}\n";
echo "====================================================\n\n";

// Test 1: Vérifier que la route existe
echo "1️⃣ Vérification de la route...\n";

$routesFile = 'routes/api.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    if (strpos($content, 'start/{product}') !== false) {
        echo "✅ Route trouvée dans routes/api.php\n";
    } else {
        echo "❌ Route NON trouvée dans routes/api.php\n";
    }
} else {
    echo "❌ Fichier routes/api.php non trouvé\n";
}

// Test 2: Vérifier le contrôleur
echo "\n2️⃣ Vérification du contrôleur...\n";

$controllerFile = 'app/Http/Controllers/API/ConversationController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'startProductConversation') !== false) {
        echo "✅ Méthode startProductConversation trouvée\n";
    } else {
        echo "❌ Méthode startProductConversation NON trouvée\n";
    }
} else {
    echo "❌ Fichier ConversationController.php non trouvé\n";
}

// Test 3: Vérifier les middlewares
echo "\n3️⃣ Vérification des middlewares...\n";

$kernelFile = 'app/Http/Kernel.php';
if (file_exists($kernelFile)) {
    $content = file_get_contents($kernelFile);
    if (strpos($content, 'auth:sanctum') !== false) {
        echo "✅ Middleware auth:sanctum trouvé\n";
    } else {
        echo "❌ Middleware auth:sanctum NON trouvé\n";
    }
} else {
    echo "❌ Fichier Kernel.php non trouvé\n";
}

// Test 4: Vérifier la structure des routes
echo "\n4️⃣ Structure des routes de conversation...\n";

$routesContent = file_get_contents('routes/api.php');
$lines = explode("\n", $routesContent);

$inConversationsGroup = false;
$conversationRoutes = [];

foreach ($lines as $lineNumber => $line) {
    $line = trim($line);
    
    if (strpos($line, 'Route::prefix(\'conversations\')') !== false) {
        $inConversationsGroup = true;
        echo "   📍 Groupe conversations trouvé ligne " . ($lineNumber + 1) . "\n";
        continue;
    }
    
    if ($inConversationsGroup) {
        if (strpos($line, 'Route::') !== false) {
            $conversationRoutes[] = $line;
            echo "   🔗 " . trim($line) . "\n";
        }
        
        // Sortir du groupe si on trouve un autre groupe
        if (strpos($line, 'Route::prefix(') !== false && strpos($line, 'conversations') === false) {
            $inConversationsGroup = false;
        }
    }
}

// Test 5: Vérifier l'ordre des routes
echo "\n5️⃣ Ordre des routes de conversation...\n";

$startRouteFound = false;
$startRouteIndex = -1;

foreach ($conversationRoutes as $index => $route) {
    if (strpos($route, 'start/{product}') !== false) {
        $startRouteFound = true;
        $startRouteIndex = $index;
        echo "   ✅ Route start/{product} trouvée à l'index $index\n";
        break;
    }
}

if ($startRouteFound) {
    echo "   📍 Position de la route start/{product}: $startRouteIndex\n";
    
    // Vérifier s'il y a des routes avec paramètres avant
    $hasParamRoutesBefore = false;
    for ($i = 0; $i < $startRouteIndex; $i++) {
        if (strpos($conversationRoutes[$i], '{') !== false) {
            $hasParamRoutesBefore = true;
            echo "   ⚠️  Route avec paramètre trouvée avant start/{product}: " . trim($conversationRoutes[$i]) . "\n";
        }
    }
    
    if ($hasParamRoutesBefore) {
        echo "   🚨 PROBLÈME: Des routes avec paramètres sont définies AVANT start/{product}\n";
        echo "   💡 Solution: Déplacer start/{product} AVANT les routes avec paramètres\n";
    } else {
        echo "   ✅ Ordre des routes correct\n";
    }
} else {
    echo "   ❌ Route start/{product} NON trouvée\n";
}

echo "\n🎯 Résumé du diagnostic\n";
echo "======================\n";

if ($startRouteFound && !$hasParamRoutesBefore) {
    echo "✅ Route correctement configurée\n";
    echo "🔍 Vérifiez maintenant l'authentification et les middlewares\n";
} else {
    echo "❌ Problème de configuration détecté\n";
    echo "🔧 Corrigez l'ordre des routes ou la configuration\n";
}

echo "\n💡 Pour tester l'API:\n";
echo "curl -X POST 'http://localhost:8000/api/v1/conversations/start/2' \\\n";
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '{\"message\": \"Test message\"}'\n";
?>

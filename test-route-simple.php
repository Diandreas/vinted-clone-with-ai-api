<?php
/**
 * Test simple de la route de conversation
 */

echo "🧪 Test simple de la route /conversations/start/2\n";
echo "==============================================\n\n";

// Test 1: Vérifier que la route existe dans le fichier
echo "1️⃣ Vérification de la route dans routes/api.php...\n";

$routesFile = 'routes/api.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    
    if (strpos($content, 'start/{product}') !== false) {
        echo "   ✅ Route start/{product} trouvée\n";
        
        // Vérifier l'ordre
        $lines = explode("\n", $content);
        $inConversationsGroup = false;
        $startRouteLine = -1;
        $conversationRouteLine = -1;
        
        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            
            if (strpos($line, 'Route::prefix(\'conversations\')') !== false) {
                $inConversationsGroup = true;
                continue;
            }
            
            if ($inConversationsGroup) {
                if (strpos($line, 'start/{product}') !== false) {
                    $startRouteLine = $lineNumber + 1;
                }
                
                if (strpos($line, '{conversation}') !== false) {
                    $conversationRouteLine = $lineNumber + 1;
                }
                
                // Sortir du groupe si on trouve un autre groupe
                if (strpos($line, 'Route::prefix(') !== false && strpos($line, 'conversations') === false) {
                    $inConversationsGroup = false;
                }
            }
        }
        
        if ($startRouteLine > 0 && $conversationRouteLine > 0) {
            if ($startRouteLine < $conversationRouteLine) {
                echo "   ✅ Ordre correct: start/{product} (ligne $startRouteLine) AVANT {conversation} (ligne $conversationRouteLine)\n";
            } else {
                echo "   ❌ Ordre incorrect: start/{product} (ligne $startRouteLine) APRÈS {conversation} (ligne $conversationRouteLine)\n";
            }
        }
        
    } else {
        echo "   ❌ Route start/{product} NON trouvée\n";
    }
} else {
    echo "   ❌ Fichier routes/api.php non trouvé\n";
}

// Test 2: Vérifier le contrôleur
echo "\n2️⃣ Vérification du contrôleur...\n";

$controllerFile = 'app/Http/Controllers/API/ConversationController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    if (strpos($content, 'startProductConversation') !== false) {
        echo "   ✅ Méthode startProductConversation trouvée\n";
    } else {
        echo "   ❌ Méthode startProductConversation NON trouvée\n";
    }
    
    if (strpos($content, 'class ConversationController') !== false) {
        echo "   ✅ Classe ConversationController trouvée\n";
    } else {
        echo "   ❌ Classe ConversationController NON trouvée\n";
    }
} else {
    echo "   ❌ Fichier ConversationController.php non trouvé\n";
}

// Test 3: Vérifier les middlewares
echo "\n3️⃣ Vérification des middlewares...\n";

if (strpos($content, 'auth:sanctum') !== false) {
    echo "   ✅ Middleware auth:sanctum trouvé\n";
} else {
    echo "   ❌ Middleware auth:sanctum NON trouvé\n";
}

// Test 4: Vérifier le cache
echo "\n4️⃣ Vérification du cache...\n";

$routeCacheFile = 'bootstrap/cache/routes.php';
if (file_exists($routeCacheFile)) {
    echo "   ⚠️  Cache des routes présent (peut causer des problèmes)\n";
    echo "   💡 Recommandé: Supprimer le cache\n";
} else {
    echo "   ✅ Aucun cache de routes trouvé\n";
}

echo "\n🎯 Résumé du diagnostic:\n";
echo "======================\n";

if (strpos($content, 'start/{product}') !== false && 
    strpos($content, 'startProductConversation') !== false) {
    echo "✅ Configuration des routes correcte\n";
    echo "🔧 Prochaines étapes:\n";
    echo "   1. Nettoyer le cache: php clear-cache.php\n";
    echo "   2. Redémarrer le serveur Laravel\n";
    echo "   3. Tester la route: php test-conversation-direct.php\n";
} else {
    echo "❌ Problème de configuration détecté\n";
    echo "🔧 Vérifiez les routes et le contrôleur\n";
}

echo "\n📝 Pour nettoyer le cache:\n";
echo "php clear-cache.php\n\n";

echo "📝 Pour tester la route:\n";
echo "php test-conversation-direct.php\n";
?>

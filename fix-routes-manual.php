<?php
/**
 * Correction manuelle des routes de conversation
 * Réorganise les routes pour éviter le conflit start/{product} vs {conversation}
 */

echo "🔧 Correction manuelle des routes de conversation\n";
echo "==============================================\n\n";

// Lire le fichier de routes
$routesFile = 'routes/api.php';
if (!file_exists($routesFile)) {
    echo "❌ Fichier routes/api.php non trouvé\n";
    exit(1);
}

$content = file_get_contents($routesFile);

// Créer une sauvegarde
$backupFile = 'routes/api.php.backup.' . date('Y-m-d-H-i-s');
if (copy($routesFile, $backupFile)) {
    echo "💾 Sauvegarde créée: $backupFile\n\n";
}

// Nouvelle structure des routes de conversation
$newConversationRoutes = '        // Conversation & Messages Routes
        Route::prefix(\'conversations\')->group(function () {
            // Routes spécifiques (AVANT les routes avec paramètres)
            Route::get(\'/\', [ConversationController::class, \'index\']);
            Route::post(\'/\', [ConversationController::class, \'store\']);
            Route::get(\'my-product-discussions\', [ConversationController::class, \'myProductDiscussions\']);
            Route::get(\'my-products-with-buyers\', [ConversationController::class, \'myProductsWithBuyers\']);
            Route::get(\'my-product-interests\', [ConversationController::class, \'myProductInterests\']);
            Route::post(\'start/{product}\', [ConversationController::class, \'startProductConversation\']);
            Route::get(\'product/{product}/conversations\', [ConversationController::class, \'getProductConversations\']);
            
            // Routes avec paramètres (APRÈS les routes spécifiques)
            Route::get(\'{conversation}\', [ConversationController::class, \'show\']);
            Route::delete(\'{conversation}\', [ConversationController::class, \'destroy\']);
            Route::post(\'{conversation}/messages\', [MessageController::class, \'store\']);
            Route::get(\'{conversation}/messages\', [MessageController::class, \'index\']);
            Route::put(\'{conversation}/status\', [ConversationController::class, \'updateStatus\']);
            Route::put(\'messages/{message}/read\', [MessageController::class, \'markAsRead\']);
            Route::delete(\'messages/{message}\', [MessageController::class, \'destroy\']);
            Route::post(\'messages/{message}/report\', [MessageController::class, \'report\']);
        });';

echo "1️⃣ Remplacement des routes de conversation...\n";

// Remplacer la section des routes de conversation
$pattern = '/\/\/ Conversation & Messages Routes\s+Route::prefix\(\'conversations\'\)->group\(function \(\) \{[\s\S]*?\}\);(\s+)/';
$replacement = $newConversationRoutes . '$1';

if (preg_match($pattern, $content)) {
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if (file_put_contents($routesFile, $newContent)) {
        echo "✅ Routes de conversation corrigées\n";
    } else {
        echo "❌ Erreur lors de l'écriture du fichier\n";
        exit(1);
    }
} else {
    echo "❌ Section des routes de conversation non trouvée\n";
    echo "💡 Vérifiez le fichier routes/api.php\n";
    exit(1);
}

echo "\n2️⃣ Vérification de la correction...\n";

// Vérifier que la route start/{product} est bien présente
$finalContent = file_get_contents($routesFile);
if (strpos($finalContent, 'start/{product}') !== false) {
    echo "✅ Route start/{product} présente\n";
} else {
    echo "❌ Route start/{product} manquante\n";
    exit(1);
}

// Vérifier l'ordre des routes
$lines = explode("\n", $finalContent);
$inConversationsGroup = false;
$startRouteFound = false;
$conversationRouteFound = false;

foreach ($lines as $lineNumber => $line) {
    $line = trim($line);
    
    if (strpos($line, 'Route::prefix(\'conversations\')') !== false) {
        $inConversationsGroup = true;
        continue;
    }
    
    if ($inConversationsGroup) {
        if (strpos($line, 'start/{product}') !== false) {
            $startRouteFound = true;
            echo "✅ Route start/{product} trouvée ligne " . ($lineNumber + 1) . "\n";
        }
        
        if (strpos($line, '{conversation}') !== false) {
            $conversationRouteFound = true;
            echo "✅ Route {conversation} trouvée ligne " . ($lineNumber + 1) . "\n";
        }
        
        // Sortir du groupe si on trouve un autre groupe
        if (strpos($line, 'Route::prefix(') !== false && strpos($line, 'conversations') === false) {
            $inConversationsGroup = false;
        }
    }
}

if ($startRouteFound && $conversationRouteFound) {
    echo "✅ Ordre des routes correct\n";
} else {
    echo "❌ Problème avec l'ordre des routes\n";
    exit(1);
}

echo "\n3️⃣ Actions recommandées:\n";
echo "======================\n";
echo "1. Nettoyer le cache des routes:\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "2. Vérifier que le serveur fonctionne:\n";
echo "   php artisan serve\n\n";

echo "3. Tester la route:\n";
echo "   php test-conversation-direct.php\n\n";

echo "4. Ou tester manuellement:\n";
echo "   curl -X POST 'http://localhost:8000/api/v1/conversations/start/2' \\\n";
echo "     -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"message\": \"Test message\"}'\n\n";

echo "🔧 Routes corrigées avec succès !\n";
echo "💡 La route start/{product} est maintenant AVANT {conversation}\n";
echo "📝 Sauvegarde disponible: $backupFile\n";
?>

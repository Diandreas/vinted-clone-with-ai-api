<?php
/**
 * Script pour corriger l'ordre des routes de conversation
 * Le problème : la route {conversation} intercepte start/{product}
 */

echo "🔧 Correction de l'ordre des routes de conversation\n";
echo "==================================================\n\n";

// Lire le fichier de routes
$routesFile = 'routes/api.php';
if (!file_exists($routesFile)) {
    echo "❌ Fichier routes/api.php non trouvé\n";
    exit(1);
}

$content = file_get_contents($routesFile);

// Vérifier l'ordre actuel
echo "1️⃣ Vérification de l'ordre actuel des routes...\n";

$lines = explode("\n", $content);
$inConversationsGroup = false;
$conversationRoutes = [];
$startRouteLine = -1;
$paramRoutesLines = [];

foreach ($lines as $lineNumber => $line) {
    $line = trim($line);
    
    if (strpos($line, 'Route::prefix(\'conversations\')') !== false) {
        $inConversationsGroup = true;
        echo "   📍 Groupe conversations trouvé ligne " . ($lineNumber + 1) . "\n";
        continue;
    }
    
    if ($inConversationsGroup) {
        if (strpos($line, 'Route::') !== false) {
            $conversationRoutes[] = [
                'line' => $line,
                'number' => $lineNumber + 1,
                'content' => $line
            ];
            
            if (strpos($line, 'start/{product}') !== false) {
                $startRouteLine = $lineNumber + 1;
                echo "   ✅ Route start/{product} ligne $startRouteLine\n";
            }
            
            if (strpos($line, '{conversation}') !== false) {
                $paramRoutesLines[] = $lineNumber + 1;
                echo "   ⚠️  Route avec paramètre ligne " . ($lineNumber + 1) . ": " . trim($line) . "\n";
            }
        }
        
        // Sortir du groupe si on trouve un autre groupe
        if (strpos($line, 'Route::prefix(') !== false && strpos($line, 'conversations') === false) {
            $inConversationsGroup = false;
        }
    }
}

// Vérifier s'il y a un problème d'ordre
$hasOrderProblem = false;
foreach ($paramRoutesLines as $paramLine) {
    if ($paramLine < $startRouteLine) {
        $hasOrderProblem = true;
        break;
    }
}

if ($hasOrderProblem) {
    echo "\n🚨 PROBLÈME DÉTECTÉ: Routes avec paramètres AVANT start/{product}\n";
    echo "💡 Solution: Réorganiser les routes\n\n";
    
    // Créer une version corrigée
    echo "2️⃣ Création de la version corrigée...\n";
    
    $newContent = '';
    $inConversationsGroup = false;
    $conversationSection = '';
    $otherSections = '';
    $closingBrace = '';
    
    foreach ($lines as $line) {
        if (strpos($line, 'Route::prefix(\'conversations\')') !== false) {
            $inConversationsGroup = true;
            $newContent .= $line . "\n";
            continue;
        }
        
        if ($inConversationsGroup) {
            if (strpos($line, 'Route::') !== false) {
                // Séparer les routes spécifiques des routes avec paramètres
                if (strpos($line, '{') !== false) {
                    // Route avec paramètre - la mettre à la fin
                    $otherSections .= $line . "\n";
                } else {
                    // Route spécifique - la garder au début
                    $conversationSection .= $line . "\n";
                }
            } elseif (strpos($line, '});') !== false) {
                // Fermeture du groupe
                $closingBrace = $line;
                break;
            } else {
                $conversationSection .= $line . "\n";
            }
        } else {
            $newContent .= $line . "\n";
        }
    }
    
    // Ajouter les sections dans le bon ordre
    $newContent .= $conversationSection;
    $newContent .= $otherSections;
    $newContent .= $closingBrace . "\n";
    
    // Sauvegarder la version corrigée
    $backupFile = 'routes/api.php.backup.' . date('Y-m-d-H-i-s');
    if (copy($routesFile, $backupFile)) {
        echo "   💾 Sauvegarde créée: $backupFile\n";
    }
    
    if (file_put_contents($routesFile, $newContent)) {
        echo "   ✅ Routes corrigées dans routes/api.php\n";
    } else {
        echo "   ❌ Erreur lors de l'écriture du fichier\n";
    }
    
} else {
    echo "\n✅ Ordre des routes correct\n";
    echo "🔍 Le problème pourrait venir d'ailleurs\n";
}

echo "\n3️⃣ Vérification finale...\n";

// Vérifier que la route start/{product} est bien accessible
$finalContent = file_get_contents($routesFile);
if (strpos($finalContent, 'start/{product}') !== false) {
    echo "   ✅ Route start/{product} toujours présente\n";
} else {
    echo "   ❌ Route start/{product} perdue lors de la correction\n";
}

echo "\n🎯 Actions recommandées:\n";
echo "======================\n";
echo "1. Nettoyer le cache des routes:\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "2. Vérifier que le serveur fonctionne:\n";
echo "   php artisan serve\n\n";

echo "3. Tester la route:\n";
echo "   curl -X POST 'http://localhost:8000/api/v1/conversations/start/2' \\\n";
echo "     -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"message\": \"Test message\"}'\n\n";

echo "4. Vérifier les logs Laravel:\n";
echo "   tail -f storage/logs/laravel.log\n\n";

if ($hasOrderProblem) {
    echo "🔧 Routes réorganisées pour résoudre le conflit d'ordre\n";
    echo "💡 La route start/{product} est maintenant AVANT {conversation}\n";
} else {
    echo "🔍 Problème non lié à l'ordre des routes\n";
    echo "💡 Vérifiez l'authentification et les middlewares\n";
}

echo "\n5. Vérifier que le contrôleur existe:\n";
echo "   ls -la app/Http/Controllers/API/ConversationController.php\n\n";

echo "6. Vérifier que la méthode existe:\n";
echo "   grep -n 'startProductConversation' app/Http/Controllers/API/ConversationController.php\n";
?>


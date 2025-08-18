<?php
/**
 * Script de vérification finale pour l'affichage des messages
 */

echo "🔍 Vérification finale de l'affichage des messages\n";
echo "===============================================\n\n";

// Composants à vérifier
$components = [
    'resources/js/views/ChatHub.vue',
    'resources/js/views/ConversationDetail.vue',
    'resources/js/views/ProductDetail.vue',
    'resources/js/views/ProductDiscussions.vue',
    'resources/js/views/SellerProductConversations.vue',
    'resources/js/views/Messages.vue'
];

echo "1️⃣ Vérification des composants...\n";
echo "================================\n";

$allFixed = true;

foreach ($components as $componentFile) {
    if (!file_exists($componentFile)) {
        echo "   ⚠️  Fichier non trouvé: $componentFile\n";
        continue;
    }
    
    $content = file_get_contents($componentFile);
    $filename = basename($componentFile);
    
    // Vérifier si le composant utilise extractMessageContent
    if (strpos($content, 'extractMessageContent') !== false) {
        echo "   ✅ $filename - Utilise extractMessageContent\n";
    }
    // Vérifier si le composant a sa propre logique de parsing JSON
    elseif (strpos($content, 'safeParseMaybeJson') !== false) {
        echo "   ✅ $filename - Utilise safeParseMaybeJson (logique personnalisée)\n";
    }
    // Vérifier s'il y a encore des affichages directs de .content
    elseif (preg_match('/\{\{\s*[^}]+\.[^}]*\.content[^}]*\}\}/', $content)) {
        echo "   ❌ $filename - AFFICHAGE DIRECT DE .content DÉTECTÉ !\n";
        $allFixed = false;
        
        // Afficher les lignes problématiques
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/\{\{\s*[^}]+\.[^}]*\.content[^}]*\}\}/', $line)) {
                echo "      Ligne " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
    else {
        echo "   ℹ️  $filename - Aucun affichage de message détecté\n";
    }
}

echo "\n2️⃣ Vérification des imports...\n";
echo "=============================\n";

foreach ($components as $componentFile) {
    if (!file_exists($componentFile)) continue;
    
    $content = file_get_contents($componentFile);
    $filename = basename($componentFile);
    
    if (strpos($content, 'extractMessageContent') !== false) {
        if (strpos($content, 'import.*extractMessageContent') !== false || 
            strpos($content, 'import { extractMessageContent }') !== false) {
            echo "   ✅ $filename - Import correct\n";
        } else {
            echo "   ❌ $filename - Utilise extractMessageContent mais IMPORT MANQUANT !\n";
            $allFixed = false;
        }
    }
}

echo "\n3️⃣ Recherche d'autres affichages de messages...\n";
echo "=============================================\n";

// Chercher dans tous les composants Vue
$vueFiles = glob('resources/js/views/*.vue');
$otherComponents = [];

foreach ($vueFiles as $vueFile) {
    if (in_array($vueFile, $components)) continue; // Déjà vérifié
    
    $content = file_get_contents($vueFile);
    $filename = basename($vueFile);
    
    // Chercher des affichages de .content
    if (preg_match('/\{\{\s*[^}]+\.[^}]*\.content[^}]*\}\}/', $content)) {
        $otherComponents[] = $filename;
        echo "   ⚠️  $filename - Affichage de .content détecté\n";
    }
}

if (empty($otherComponents)) {
    echo "   ✅ Aucun autre composant avec affichage de .content détecté\n";
} else {
    echo "\n   📝 Composants à vérifier manuellement :\n";
    foreach ($otherComponents as $comp) {
        echo "      - $comp\n";
    }
}

echo "\n🎯 Résumé de la vérification :\n";
echo "============================\n";

if ($allFixed && empty($otherComponents)) {
    echo "✅ TOUS LES COMPOSANTS SONT CORRIGÉS !\n";
    echo "🎉 L'affichage JSON des messages devrait être résolu partout.\n";
} elseif ($allFixed) {
    echo "✅ Composants principaux corrigés\n";
    echo "⚠️  Vérifiez manuellement les composants listés ci-dessus\n";
} else {
    echo "❌ IL RESTE DES COMPOSANTS À CORRIGER !\n";
    echo "🔧 Utilisez le script fix-message-display.php pour les corriger\n";
}

echo "\n📝 Prochaines étapes :\n";
echo "1. Rechargez votre application\n";
echo "2. Testez l'interface de chat\n";
echo "3. Vérifiez que les messages JSON s'affichent correctement\n";
echo "4. Si des problèmes persistent, vérifiez les composants listés ci-dessus\n";

echo "\n🔧 Scripts disponibles :\n";
echo "- fix-message-display.php : Correction automatique\n";
echo "- verify-message-fix.php : Vérification (ce script)\n";
?>

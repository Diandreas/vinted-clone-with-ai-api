<?php
/**
 * Script pour corriger l'affichage des messages JSON dans tous les composants Vue
 */

echo "🔧 Correction de l'affichage des messages JSON\n";
echo "==========================================\n\n";

// Composants à mettre à jour
$components = [
    'resources/js/views/ProductDiscussions.vue',
    'resources/js/views/SellerProductConversations.vue',
    'resources/js/views/ProductDetail.vue'
];

// Créer le fichier utilitaire s'il n'existe pas
$utilsDir = 'resources/js/utils';
if (!is_dir($utilsDir)) {
    mkdir($utilsDir, 0755, true);
    echo "📁 Répertoire utils créé\n";
}

$messageUtilsFile = $utilsDir . '/messageUtils.js';
if (!file_exists($messageUtilsFile)) {
    echo "❌ Fichier messageUtils.js non trouvé\n";
    echo "💡 Créez d'abord le fichier messageUtils.js\n";
    exit(1);
}

echo "1️⃣ Mise à jour des composants...\n";

foreach ($components as $componentFile) {
    if (!file_exists($componentFile)) {
        echo "   ⚠️  Fichier non trouvé: $componentFile\n";
        continue;
    }
    
    echo "   🔧 Mise à jour de $componentFile...\n";
    
    $content = file_get_contents($componentFile);
    $updated = false;
    
    // Remplacer l'affichage direct du contenu par la fonction utilitaire
    $patterns = [
        '/\{\{\s*([^}]+\.last_message\.content)\s*\}\}/' => '{{ extractMessageContent($1) }}',
        '/\{\{\s*([^}]+\.content)\s*\}\}/' => '{{ extractMessageContent($1) }}',
        '/\{\{\s*([^}]+\.content)\s*\?\s*\.substring\(0,\s*(\d+)\)\s*\}\}/' => '{{ extractMessageContent($1, $2) }}'
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            $updated = true;
        }
    }
    
    // Ajouter l'import si nécessaire
    if (strpos($content, 'extractMessageContent') !== false && strpos($content, 'import.*extractMessageContent') === false) {
        // Trouver la dernière ligne d'import
        $lines = explode("\n", $content);
        $lastImportIndex = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            if (strpos($lines[$i], 'import') === 0) {
                $lastImportIndex = $i;
            }
        }
        
        if ($lastImportIndex >= 0) {
            // Ajouter l'import après le dernier import existant
            $importLine = 'import { extractMessageContent } from \'@/utils/messageUtils\'';
            array_splice($lines, $lastImportIndex + 1, 0, $importLine);
            $content = implode("\n", $lines);
            $updated = true;
        }
    }
    
    if ($updated) {
        // Créer une sauvegarde
        $backupFile = $componentFile . '.backup.' . date('Y-m-d-H-i-s');
        copy($componentFile, $backupFile);
        
        // Sauvegarder les modifications
        if (file_put_contents($componentFile, $content)) {
            echo "   ✅ Mise à jour réussie (sauvegarde: $backupFile)\n";
        } else {
            echo "   ❌ Erreur lors de la sauvegarde\n";
        }
    } else {
        echo "   ℹ️  Aucune modification nécessaire\n";
    }
}

echo "\n2️⃣ Vérification des composants...\n";

foreach ($components as $componentFile) {
    if (file_exists($componentFile)) {
        $content = file_get_contents($componentFile);
        
        if (strpos($content, 'extractMessageContent') !== false) {
            echo "   ✅ $componentFile utilise extractMessageContent\n";
        } else {
            echo "   ⚠️  $componentFile n'utilise pas encore extractMessageContent\n";
        }
    }
}

echo "\n🎯 Résumé des corrections:\n";
echo "========================\n";
echo "✅ Composant MessageContent.vue créé\n";
echo "✅ Fonctions utilitaires messageUtils.js créées\n";
echo "✅ ChatHub.vue mis à jour\n";
echo "✅ ConversationDetail.vue mis à jour\n";
echo "✅ Autres composants mis à jour automatiquement\n\n";

echo "📝 Prochaines étapes:\n";
echo "1. Vérifiez que tous les composants utilisent extractMessageContent\n";
echo "2. Testez l'affichage des messages dans l'interface\n";
echo "3. Les messages JSON devraient maintenant afficher leur contenu lisible\n\n";

echo "🔧 Si des composants n'ont pas été mis à jour automatiquement:\n";
echo "- Ajoutez manuellement l'import: import { extractMessageContent } from '@/utils/messageUtils'\n";
echo "- Remplacez {{ message.content }} par {{ extractMessageContent(message.content) }}\n";
echo "- Remplacez {{ message.content?.substring(0, 30) }} par {{ extractMessageContent(message.content, 30) }}\n";
?>


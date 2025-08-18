<?php
/**
 * Script simple pour nettoyer le cache Laravel
 */

echo "🧹 Nettoyage du cache Laravel\n";
echo "============================\n\n";

// Vérifier que nous sommes dans le bon répertoire
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Assurez-vous d'être dans le répertoire Laravel.\n";
    exit(1);
}

echo "1️⃣ Suppression du cache des routes...\n";
$routeCacheFile = 'bootstrap/cache/routes.php';
if (file_exists($routeCacheFile)) {
    if (unlink($routeCacheFile)) {
        echo "   ✅ Cache des routes supprimé\n";
    } else {
        echo "   ❌ Erreur lors de la suppression du cache des routes\n";
    }
} else {
    echo "   ℹ️  Aucun cache de routes trouvé\n";
}

echo "\n2️⃣ Suppression du cache de configuration...\n";
$configCacheFile = 'bootstrap/cache/config.php';
if (file_exists($configCacheFile)) {
    if (unlink($configCacheFile)) {
        echo "   ✅ Cache de configuration supprimé\n";
    } else {
        echo "   ❌ Erreur lors de la suppression du cache de configuration\n";
    }
} else {
    echo "   ℹ️  Aucun cache de configuration trouvé\n";
}

echo "\n3️⃣ Suppression du cache d'application...\n";
$appCacheFile = 'bootstrap/cache/packages.php';
if (file_exists($appCacheFile)) {
    if (unlink($appCacheFile)) {
        echo "   ✅ Cache d'application supprimé\n";
    } else {
        echo "   ❌ Erreur lors de la suppression du cache d'application\n";
    }
} else {
    echo "   ℹ️  Aucun cache d'application trouvé\n";
}

echo "\n4️⃣ Nettoyage du répertoire storage/framework/cache...\n";
$cacheDir = 'storage/framework/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            if (unlink($file)) {
                $deletedCount++;
            }
        }
    }
    echo "   ✅ $deletedCount fichiers de cache supprimés\n";
} else {
    echo "   ℹ️  Répertoire de cache non trouvé\n";
}

echo "\n5️⃣ Nettoyage du répertoire storage/framework/views...\n";
$viewsDir = 'storage/framework/views';
if (is_dir($viewsDir)) {
    $files = glob($viewsDir . '/*');
    $deletedCount = 0;
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            if (unlink($file)) {
                $deletedCount++;
            }
        }
    }
    echo "   ✅ $deletedCount fichiers de vues supprimés\n";
} else {
    echo "   ℹ️  Répertoire de vues non trouvé\n";
}

echo "\n🎯 Cache nettoyé avec succès !\n";
echo "============================\n\n";

echo "📝 Actions recommandées:\n";
echo "1. Redémarrer le serveur Laravel:\n";
echo "   php artisan serve\n\n";

echo "2. Tester la route de conversation:\n";
echo "   php test-conversation-direct.php\n\n";

echo "3. Ou tester manuellement:\n";
echo "   curl -X POST 'http://localhost:8000/api/v1/conversations/start/2' \\\n";
echo "     -H 'Authorization: Bearer YOUR_TOKEN' \\\n";
echo "     -H 'Content-Type: application/json' \\\n";
echo "     -d '{\"message\": \"Test message\"}'\n\n";

echo "✅ Le cache a été nettoyé. Les routes devraient maintenant fonctionner correctement.\n";
?>


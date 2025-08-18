<?php
/**
 * Script de diagnostic pour les images sur le VPS
 * Usage: php diagnose-images-vps.php
 */

echo "🔍 Diagnostic des Images sur le VPS\n";
echo "==================================\n\n";

// 1. Vérifier la structure des dossiers
echo "1️⃣ Structure des dossiers:\n";
echo "----------------------------\n";

$storagePath = storage_path('app/public');
$productsPath = $storagePath . '/products';

echo "Storage path: {$storagePath}\n";
echo "Products path: {$productsPath}\n\n";

if (is_dir($storagePath)) {
    echo "✅ Dossier storage existe\n";
} else {
    echo "❌ Dossier storage n'existe pas\n";
}

if (is_dir($productsPath)) {
    echo "✅ Dossier products existe\n";
} else {
    echo "❌ Dossier products n'existe pas\n";
}

echo "\n";

// 2. Lister les fichiers dans products
echo "2️⃣ Fichiers dans products:\n";
echo "---------------------------\n";

if (is_dir($productsPath)) {
    $files = scandir($productsPath);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) &&
               (strpos($file, '.png') !== false ||
                strpos($file, '.jpg') !== false ||
                strpos($file, '.jpeg') !== false);
    });

    if (empty($imageFiles)) {
        echo "❌ Aucun fichier image trouvé\n";
    } else {
        echo "✅ " . count($imageFiles) . " fichiers image trouvés:\n";
        foreach ($imageFiles as $file) {
            $filePath = $productsPath . '/' . $file;
            $size = filesize($filePath);
            $perms = substr(sprintf('%o', fileperms($filePath)), -4);
            echo "  - {$file} ({$size} bytes, permissions: {$perms})\n";
        }
    }
} else {
    echo "❌ Dossier products inaccessible\n";
}

echo "\n";

// 3. Vérifier le lien symbolique
echo "3️⃣ Lien symbolique storage:\n";
echo "----------------------------\n";

$publicStoragePath = public_path('storage');
$publicStorageProductsPath = $publicStoragePath . '/products';

echo "Public storage path: {$publicStoragePath}\n";
echo "Public storage products path: {$publicStorageProductsPath}\n\n";

if (is_link($publicStoragePath)) {
    echo "✅ Lien symbolique storage existe\n";
    $target = readlink($publicStoragePath);
    echo "  → Pointe vers: {$target}\n";
} else {
    echo "❌ Lien symbolique storage n'existe pas\n";
}

if (is_dir($publicStorageProductsPath)) {
    echo "✅ Dossier public storage products accessible\n";
} else {
    echo "❌ Dossier public storage products inaccessible\n";
}

echo "\n";

// 4. Vérifier les permissions
echo "4️⃣ Permissions:\n";
echo "----------------\n";

if (is_dir($storagePath)) {
    $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
    echo "Storage permissions: {$perms}\n";
}

if (is_dir($productsPath)) {
    $perms = substr(sprintf('%o', fileperms($productsPath)), -4);
    echo "Products permissions: {$perms}\n";
}

if (is_dir($publicStoragePath)) {
    $perms = substr(sprintf('%o', fileperms($publicStoragePath)), -4);
    echo "Public storage permissions: {$perms}\n";
}

echo "\n";

// 5. Vérifier la configuration Laravel
echo "5️⃣ Configuration Laravel:\n";
echo "---------------------------\n";

echo "APP_URL: " . config('app.url') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "Filesystem default: " . config('filesystems.default') . "\n";

$publicDisk = config('filesystems.disks.public');
if ($publicDisk) {
    echo "Public disk root: " . $publicDisk['root'] . "\n";
    echo "Public disk url: " . $publicDisk['url'] . "\n";
}

echo "\n";

// 6. Tester l'API des fichiers
echo "6️⃣ Test de l'API des fichiers:\n";
echo "--------------------------------\n";

// Simuler une requête à l'API
$testFile = 'products/1755447670_1_0.png'; // Fichier qui existe localement
$testPath = $storagePath . '/' . $testFile;

echo "Test avec le fichier: {$testFile}\n";

if (file_exists($testPath)) {
    echo "✅ Fichier existe dans storage\n";

    // Simuler le FileController
    $filePath = 'public/' . $testFile;
    if (Storage::exists($filePath)) {
        echo "✅ Fichier accessible via Storage facade\n";
        $mimeType = Storage::mimeType($filePath);
        echo "  MIME type: {$mimeType}\n";
    } else {
        echo "❌ Fichier non accessible via Storage facade\n";
    }
} else {
    echo "❌ Fichier n'existe pas dans storage\n";
}

echo "\n";

// 7. Recommandations
echo "7️⃣ Recommandations:\n";
echo "--------------------\n";

if (!is_dir($productsPath)) {
    echo "  - Créer le dossier: mkdir -p {$productsPath}\n";
}

if (!is_link($publicStoragePath)) {
    echo "  - Créer le lien symbolique: php artisan storage:link\n";
}

if (is_dir($productsPath) && empty(scandir($productsPath))) {
    echo "  - Le dossier products est vide, vérifier l'upload des images\n";
}

echo "  - Vérifier les permissions: chmod 755 storage/app/public/products\n";
echo "  - Vérifier le propriétaire: chown -R www-data:www-data storage/\n";
echo "  - Redémarrer le serveur web si nécessaire\n";

echo "\n🏁 Diagnostic terminé\n";
?>



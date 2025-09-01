<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Diagnostic Avancé des Reviews sur le VPS\n";
echo "============================================\n\n";

// 1. Vérifier la configuration de la base de données
echo "1. Configuration de la base de données:\n";
echo "   📊 Driver: " . config('database.default') . "\n";
echo "   📊 Host: " . config('database.connections.mysql.host') . "\n";
echo "   📊 Database: " . config('database.connections.mysql.database') . "\n";
echo "   📊 Username: " . config('database.connections.mysql.username') . "\n";

// 2. Test de connexion à la base de données
echo "\n2. Test de connexion à la base de données:\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Connexion à la base de données réussie\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Vérifier les tables
echo "\n3. Vérification des tables:\n";
$tables = ['users', 'reviews', 'migrations'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "   ✅ Table '$table' existe ($count enregistrements)\n";
    } else {
        echo "   ❌ Table '$table' n'existe pas\n";
    }
}

// 4. Vérifier les migrations spécifiques
echo "\n4. Vérification des migrations:\n";
try {
    $migrations = DB::table('migrations')->get();
    $reviewsMigration = $migrations->where('migration', '2025_08_06_230842_create_reviews_table')->first();
    $usersMigration = $migrations->where('migration', '0001_01_01_000000_create_users_table')->first();
    
    if ($reviewsMigration) {
        echo "   ✅ Migration reviews exécutée le: " . $reviewsMigration->created_at . "\n";
    } else {
        echo "   ❌ Migration reviews non exécutée\n";
    }
    
    if ($usersMigration) {
        echo "   ✅ Migration users exécutée le: " . $usersMigration->created_at . "\n";
    } else {
        echo "   ❌ Migration users non exécutée\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors de la vérification des migrations: " . $e->getMessage() . "\n";
}

// 5. Test des modèles
echo "\n5. Test des modèles:\n";
try {
    // Test du modèle User
    $user = \App\Models\User::find(4);
    if ($user) {
        echo "   ✅ Utilisateur ID 4 trouvé: " . $user->name . "\n";
        
        // Test de la relation receivedReviews
        $reviews = $user->receivedReviews()->get();
        echo "   📊 Reviews reçues: " . $reviews->count() . "\n";
        
        if ($reviews->count() > 0) {
            $firstReview = $reviews->first();
            echo "   📝 Première review - ID: " . $firstReview->id . "\n";
            echo "   📝 Contenu: " . substr($firstReview->content ?? 'Aucun contenu', 0, 50) . "...\n";
            echo "   📝 Note: " . $firstReview->rating . "/5\n";
        }
    } else {
        echo "   ❌ Utilisateur ID 4 non trouvé\n";
        
        // Lister les utilisateurs disponibles
        $users = \App\Models\User::take(5)->get(['id', 'name']);
        echo "   📋 Utilisateurs disponibles:\n";
        foreach ($users as $u) {
            echo "      - ID " . $u->id . ": " . $u->name . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors du test des modèles: " . $e->getMessage() . "\n";
    echo "   📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// 6. Test de l'endpoint API avec authentification
echo "\n6. Test de l'endpoint API:\n";
try {
    // Créer une requête HTTP
    $request = \Illuminate\Http\Request::create('/api/v1/users/4/reviews', 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');
    
    // Simuler un utilisateur authentifié
    $user = \App\Models\User::find(4);
    if ($user) {
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }
    
    $response = app()->handle($request);
    $status = $response->getStatusCode();
    $content = $response->getContent();
    
    echo "   📡 Status de la réponse: $status\n";
    echo "   📄 Contenu de la réponse: " . substr($content, 0, 200) . "...\n";
    
    if ($status === 200) {
        $data = json_decode($content, true);
        if (isset($data['success']) && $data['success']) {
            echo "   ✅ Endpoint fonctionne correctement\n";
            if (isset($data['data']['data'])) {
                echo "   📊 Nombre de reviews retournées: " . count($data['data']['data']) . "\n";
            }
        } else {
            echo "   ⚠️ Endpoint retourne success: false\n";
        }
    } else {
        echo "   ❌ Endpoint retourne une erreur HTTP\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors du test de l'endpoint: " . $e->getMessage() . "\n";
    echo "   📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// 7. Vérifier les permissions de fichiers
echo "\n7. Vérification des permissions:\n";
$paths = [
    'storage/logs' => 'storage/logs',
    'storage/framework/cache' => 'storage/framework/cache',
    'storage/framework/sessions' => 'storage/framework/sessions',
    'bootstrap/cache' => 'bootstrap/cache'
];

foreach ($paths as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $owner = posix_getpwuid(fileowner($path))['name'];
        echo "   📁 $name: permissions $perms, owner $owner\n";
    } else {
        echo "   ❌ $name: n'existe pas\n";
    }
}

// 8. Test de la relation Review
echo "\n8. Test de la relation Review:\n";
try {
    $review = \App\Models\Review::first();
    if ($review) {
        echo "   ✅ Première review trouvée - ID: " . $review->id . "\n";
        echo "   📝 Reviewer ID: " . $review->reviewer_id . "\n";
        echo "   📝 Reviewed ID: " . $review->reviewed_id . "\n";
        echo "   📝 Rating: " . $review->rating . "\n";
        
        // Test des relations
        if ($review->reviewer) {
            echo "   ✅ Relation reviewer fonctionne: " . $review->reviewer->name . "\n";
        } else {
            echo "   ❌ Relation reviewer ne fonctionne pas\n";
        }
        
        if ($review->reviewed) {
            echo "   ✅ Relation reviewed fonctionne: " . $review->reviewed->name . "\n";
        } else {
            echo "   ❌ Relation reviewed ne fonctionne pas\n";
        }
    } else {
        echo "   ⚠️ Aucune review trouvée dans la base de données\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors du test de la relation Review: " . $e->getMessage() . "\n";
}

echo "\n🔍 Diagnostic terminé\n";

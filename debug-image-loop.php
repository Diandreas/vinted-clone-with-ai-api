<?php
/**
 * Script de diagnostic pour identifier les rechargements d'images en boucle
 */

// Configuration
$logFile = 'image-requests.log';
$maxLogSize = 10 * 1024 * 1024; // 10MB

// Fonction de logging
function logImageRequest($url, $userAgent, $referer, $ip) {
    global $logFile, $maxLogSize;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = sprintf(
        "[%s] %s | %s | %s | %s\n",
        $timestamp,
        $ip,
        $url,
        $userAgent,
        $referer
    );
    
    // Rotation des logs si nécessaire
    if (file_exists($logFile) && filesize($logFile) > $maxLogSize) {
        rename($logFile, $logFile . '.old');
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Fonction pour analyser les logs
function analyzeLogs() {
    global $logFile;
    
    if (!file_exists($logFile)) {
        return "Aucun log trouvé.";
    }
    
    $logs = file($logFile, FILE_IGNORE_NEW_LINES);
    $analysis = [];
    
    foreach ($logs as $log) {
        if (preg_match('/\[(.*?)\] (.*?) \| (.*?) \| (.*?) \| (.*?)$/', $log, $matches)) {
            $timestamp = $matches[1];
            $ip = $matches[2];
            $url = $matches[3];
            $userAgent = $matches[4];
            $referer = $matches[5];
            
            if (!isset($analysis[$url])) {
                $analysis[$url] = [
                    'count' => 0,
                    'ips' => [],
                    'userAgents' => [],
                    'referers' => [],
                    'timestamps' => []
                ];
            }
            
            $analysis[$url]['count']++;
            $analysis[$url]['ips'][] = $ip;
            $analysis[$url]['userAgents'][] = $userAgent;
            $analysis[$url]['referers'][] = $referer;
            $analysis[$url]['timestamps'][] = $timestamp;
        }
    }
    
    return $analysis;
}

// Traitement de la requête
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Log de la requête
logImageRequest($requestUri, $userAgent, $referer, $ip);

// Si c'est une requête d'image, servir une image de test
if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $requestUri)) {
    // Créer une image de test simple
    $width = 300;
    $height = 200;
    
    $image = imagecreate($width, $height);
    $bgColor = imagecolorallocate($image, 240, 240, 240);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    
    // Ajouter du texte pour identifier l'image
    $text = "Test Image - " . date('H:i:s');
    imagestring($image, 5, 10, 10, $text, $textColor);
    
    // Ajouter des informations de debug
    imagestring($image, 3, 10, 30, "URI: " . substr($requestUri, 0, 50), $textColor);
    imagestring($image, 3, 10, 50, "IP: " . $ip, $textColor);
    imagestring($image, 3, 10, 70, "Time: " . date('Y-m-d H:i:s'), $textColor);
    
    // Définir les headers
    header('Content-Type: image/jpeg');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output de l'image
    imagejpeg($image);
    imagedestroy($image);
    exit;
}

// Interface de diagnostic
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic des Images</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .log-entry { background: #f5f5f5; padding: 10px; margin: 5px 0; border-radius: 3px; font-family: monospace; }
        .high-count { background: #ffe6e6; border-left: 4px solid #ff0000; }
        .medium-count { background: #fff2e6; border-left: 4px solid #ff9900; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .refresh-btn { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .refresh-btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic des Rechargements d'Images</h1>
        
        <div class="section">
            <h2>📊 Analyse des Logs</h2>
            <button class="refresh-btn" onclick="location.reload()">🔄 Actualiser</button>
            
            <?php
            $analysis = analyzeLogs();
            if (is_array($analysis) && !empty($analysis)) {
                echo "<table>";
                echo "<tr><th>URL</th><th>Compteur</th><th>Dernière requête</th><th>IPs uniques</th><th>Actions</th></tr>";
                
                foreach ($analysis as $url => $data) {
                    $count = $data['count'];
                    $lastTime = end($data['timestamps']);
                    $uniqueIPs = count(array_unique($data['ips']));
                    
                    $rowClass = '';
                    if ($count > 100) $rowClass = 'high-count';
                    elseif ($count > 50) $rowClass = 'medium-count';
                    
                    echo "<tr class='$rowClass'>";
                    echo "<td>" . htmlspecialchars(substr($url, 0, 80)) . "</td>";
                    echo "<td><strong>$count</strong></td>";
                    echo "<td>$lastTime</td>";
                    echo "<td>$uniqueIPs</td>";
                    echo "<td>";
                    if ($count > 10) {
                        echo "<button onclick='showDetails(\"$url\")'>Détails</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>Aucune donnée d'analyse disponible.</p>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>🚨 Problèmes Détectés</h2>
            <?php
            $problems = [];
            foreach ($analysis as $url => $data) {
                if ($data['count'] > 100) {
                    $problems[] = "URL <strong>$url</strong> : {$data['count']} requêtes (possible boucle infinie)";
                }
            }
            
            if (!empty($problems)) {
                echo "<ul>";
                foreach ($problems as $problem) {
                    echo "<li style='color: red;'>$problem</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: green;'>✅ Aucun problème majeur détecté.</p>";
            }
            ?>
        </div>
        
        <div class="section">
            <h2>🔧 Solutions Recommandées</h2>
            <ul>
                <li><strong>Vérifier les composants Vue.js</strong> pour les re-renders inutiles</li>
                <li><strong>Optimiser les watchers</strong> dans les composants d'images</li>
                <li><strong>Implémenter la mise en cache</strong> des images</li>
                <li><strong>Vérifier les boucles v-for</strong> avec des clés uniques</li>
                <li><strong>Utiliser lazy loading</strong> pour les images</li>
            </ul>
        </div>
        
        <div class="section">
            <h2>📝 Logs Récents</h2>
            <?php
            if (file_exists($logFile)) {
                $logs = file($logFile, FILE_IGNORE_NEW_LINES);
                $recentLogs = array_slice($logs, -20); // 20 dernières entrées
                
                foreach (array_reverse($recentLogs) as $log) {
                    echo "<div class='log-entry'>" . htmlspecialchars($log) . "</div>";
                }
            } else {
                echo "<p>Aucun log disponible.</p>";
            }
            ?>
        </div>
    </div>
    
    <script>
        function showDetails(url) {
            alert('Détails pour: ' + url + '\n\nVérifiez les composants Vue.js qui utilisent cette URL.');
        }
        
        // Auto-refresh toutes les 30 secondes
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

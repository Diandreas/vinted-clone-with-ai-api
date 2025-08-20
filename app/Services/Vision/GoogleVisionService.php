<?php

namespace App\Services\Vision;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleVisionService
{
    private $client;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient()
    {
        try {
            $credentialsPath = storage_path('app/google/service-account.json');
            
            Log::info('GoogleVision: Initializing client with credentials', [
                'credentials_path' => $credentialsPath,
                'file_exists' => file_exists($credentialsPath),
                'file_readable' => is_readable($credentialsPath),
                'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'yatou-440701')
            ]);

            // Méthode 1: Utiliser les credentials depuis l'environnement JSON (PRIORITÉ)
            $credentialsJson = env('GOOGLE_CLOUD_KEY_JSON');
            
            if (!empty($credentialsJson)) {
                Log::info('GoogleVision: Using credentials from environment JSON');
                
                $credentials = json_decode($credentialsJson, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in GOOGLE_CLOUD_KEY_JSON: ' . json_last_error_msg());
                }
                
                $this->client = new ImageAnnotatorClient([
                    'credentials' => $credentials
                ]);
                
                Log::info('GoogleVision: Client initialized successfully with JSON credentials');
                return;
            }

            // Méthode 2: Utiliser putenv si fichier existe
            if (file_exists($credentialsPath) && is_readable($credentialsPath)) {
                Log::info('GoogleVision: Using putenv with credentials file');
                
                putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
                
                $this->client = new ImageAnnotatorClient();
                
                Log::info('GoogleVision: Client initialized successfully with putenv');
                return;
            }

            // Méthode 3: Fallback - lire le fichier directement
            if (file_exists($credentialsPath)) {
                Log::info('GoogleVision: Using direct file read');
                
                $credentials = json_decode(file_get_contents($credentialsPath), true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON in credentials file: ' . json_last_error_msg());
                }
                
                $this->client = new ImageAnnotatorClient([
                    'credentials' => $credentials
                ]);
                
                Log::info('GoogleVision: Client initialized successfully with direct file read');
                return;
            }

            throw new Exception('No valid Google Cloud credentials found');

        } catch (Exception $e) {
            Log::error('GoogleVision: Failed to initialize client', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    public function analyzeImage($imagePath, $productId = null)
    {
        try {
            Log::info('GoogleVision: Starting image analysis', [
                'image_path' => $imagePath,
                'product_id' => $productId,
                'file_exists' => file_exists($imagePath),
                'file_size' => file_exists($imagePath) ? filesize($imagePath) : null
            ]);

            if (!file_exists($imagePath)) {
                throw new Exception("Image file not found: " . $imagePath);
            }

            // Lire l'image
            $imageContent = file_get_contents($imagePath);
            
            // Créer l'objet Image
            $image = (new Image())->setContent($imageContent);

            // Définir les features à analyser
            $features = [
                (new Feature())->setType(Feature\Type::LABEL_DETECTION)->setMaxResults(10),
                (new Feature())->setType(Feature\Type::OBJECT_LOCALIZATION)->setMaxResults(10),
                (new Feature())->setType(Feature\Type::TEXT_DETECTION)->setMaxResults(5),
            ];

            // Créer la requête
            $request = (new AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures($features);

            $batchRequest = (new BatchAnnotateImagesRequest())
                ->setRequests([$request]);

            // Exécuter l'analyse
            $response = $this->client->batchAnnotateImages($batchRequest);
            $annotations = $response->getResponses();

            if (empty($annotations)) {
                throw new Exception("No annotations returned from Google Vision API");
            }

            $annotation = $annotations[0];

            // Traiter les résultats
            $result = [
                'labels' => [],
                'objects' => [],
                'text' => [],
                'confidence' => 0
            ];

            // Labels
            foreach ($annotation->getLabelAnnotations() as $label) {
                $result['labels'][] = [
                    'description' => $label->getDescription(),
                    'score' => $label->getScore(),
                    'confidence' => $label->getScore()
                ];
            }

            // Objects
            foreach ($annotation->getLocalizedObjectAnnotations() as $object) {
                $result['objects'][] = [
                    'name' => $object->getName(),
                    'confidence' => $object->getScore()
                ];
            }

            // Text
            foreach ($annotation->getTextAnnotations() as $text) {
                $result['text'][] = [
                    'description' => $text->getDescription(),
                    'confidence' => 0.9 // Google ne fournit pas de score pour le texte
                ];
                break; // On prend seulement le premier texte global
            }

            // Calculer la confiance globale
            if (!empty($result['labels'])) {
                $result['confidence'] = $result['labels'][0]['confidence'];
            }

            Log::info('GoogleVision: Analysis completed successfully', [
                'labels_count' => count($result['labels']),
                'objects_count' => count($result['objects']),
                'text_detected' => !empty($result['text']),
                'confidence' => $result['confidence']
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('GoogleVision: Analysis failed', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'image_path' => $imagePath,
                'product_id' => $productId,
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function searchSimilarProducts($imagePath, $limit = 10)
    {
        try {
            Log::info('GoogleVision: Starting similarity search', [
                'image_path' => $imagePath,
                'limit' => $limit
            ]);

            // Analyser l'image uploadée
            $searchResults = $this->analyzeImage($imagePath);

            // Ici vous pouvez ajouter votre logique de recherche de produits similaires
            // basée sur les labels et objets détectés

            Log::info('GoogleVision: Similarity search completed', [
                'search_results' => $searchResults
            ]);

            return $searchResults;

        } catch (Exception $e) {
            Log::warning('GoogleVision: Failed to analyze search image, returning empty results');
            
            return [
                'labels' => [],
                'objects' => [],
                'text' => [],
                'confidence' => 0
            ];
        }
    }
}
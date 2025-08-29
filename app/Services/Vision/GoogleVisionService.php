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
    private $isEnabled = false;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient()
    {
        try {
            // Log::info('GoogleVision: Initializing client with putenv method');
            
            $credentialsPath = storage_path('app/google/service-account.json');
            
            // Check if credentials file exists
            if (!file_exists($credentialsPath)) {
                Log::warning('GoogleVision: Credentials file not found, service will be disabled', [
                    'expected_path' => $credentialsPath
                ]);
                $this->isEnabled = false;
                return;
            }
            
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
            
            $this->client = new ImageAnnotatorClient();
            $this->isEnabled = true;
            
            // Log::info('GoogleVision: Client initialized successfully');
            
        } catch (Exception $e) {
            Log::error('GoogleVision: Failed to initialize client, service will be disabled', [
                'error' => $e->getMessage()
            ]);
            $this->isEnabled = false;
        }
    }
    public function analyzeImage($imagePath, $productId = null)
    {
        try {
            Log::info('GoogleVision: Starting image analysis', [
                'image_path' => $imagePath,
                'product_id' => $productId,
                'service_enabled' => $this->isEnabled,
                'file_exists' => file_exists($imagePath),
                'file_size' => file_exists($imagePath) ? filesize($imagePath) : null
            ]);

            if (!$this->isEnabled) {
                Log::warning('GoogleVision: Service not enabled, returning default result');
                return [
                    'labels' => [],
                    'objects' => [],
                    'text' => [],
                    'confidence' => 0
                ];
            }

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
                'limit' => $limit,
                'service_enabled' => $this->isEnabled
            ]);

            if (!$this->isEnabled) {
                Log::warning('GoogleVision: Service not enabled, returning empty results');
                return [
                    'labels' => [],
                    'objects' => [],
                    'text' => [],
                    'confidence' => 0
                ];
            }

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
    
    /**
     * Check if the Google Vision service is properly configured and enabled
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
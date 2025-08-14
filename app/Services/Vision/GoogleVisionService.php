<?php

namespace App\Services\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use App\Models\ProductVisionData;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleVisionService
{
    private $client;
    
    public function __construct()
    {
        // Le client sera initialisé à la demande pour éviter les erreurs au démarrage
        $this->client = null;
    }

    private function initializeClient()
    {
        if ($this->client === null) {
            if (!class_exists(ImageAnnotatorClient::class)) {
                // Mode démo : simuler l'analyse sans Google Vision
                return null;
            }
            
            $this->client = new ImageAnnotatorClient([
                'credentials' => config('services.google_cloud.key_file'),
            ]);
        }
        return $this->client;
    }

    /**
     * Analyser une image avec Google Vision API
     */
    public function analyzeImage($imagePath, $productId = null)
    {
        try {
            // Vérifier si Google Vision est disponible
            $client = $this->initializeClient();
            
            // Mode démo : simuler une réponse si Google Vision n'est pas disponible
            if ($client === null) {
                return $this->generateDemoAnalysis($imagePath, $productId);
            }

            // Lire l'image
            $imageContent = file_get_contents($imagePath);
            $image = new Image();
            $image->setContent($imageContent);

            // Définir les features à extraire
            $features = [
                new Feature(['type' => Type::LABEL_DETECTION, 'max_results' => 10]),
                new Feature(['type' => Type::OBJECT_LOCALIZATION, 'max_results' => 10]),
                new Feature(['type' => Type::IMAGE_PROPERTIES, 'max_results' => 10]),
                new Feature(['type' => Type::TEXT_DETECTION, 'max_results' => 10]),
                new Feature(['type' => Type::FACE_DETECTION, 'max_results' => 10]),
                new Feature(['type' => Type::WEB_DETECTION, 'max_results' => 10]),
            ];

            // Créer la requête
            $request = new AnnotateImageRequest();
            $request->setImage($image);
            $request->setFeatures($features);

            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$request]);
            
            $response = $client->batchAnnotateImages($batchRequest);
            $annotations = $response->getResponses()[0];

            if ($annotations->hasError()) {
                Log::error('Google Vision API Error: ' . $annotations->getError()->getMessage());
                return null;
            }

            // Extraire les données
            $visionData = [
                'labels' => $this->extractLabels($annotations),
                'objects' => $this->extractObjects($annotations),
                'colors' => $this->extractColors($annotations),
                'text' => $this->extractText($annotations),
                'faces' => $this->extractFaces($annotations),
                'web_entities' => $this->extractWebEntities($annotations),
                'similar_images' => $this->extractSimilarImages($annotations),
            ];

            // Calculer le vecteur de caractéristiques
            $featureVector = $this->calculateFeatureVector($visionData);

            // Sauvegarder en base de données si un product_id est fourni
            if ($productId) {
                ProductVisionData::create([
                    'product_id' => $productId,
                    'image_path' => $imagePath,
                    'labels' => $visionData['labels'],
                    'objects' => $visionData['objects'],
                    'colors' => $visionData['colors'],
                    'text' => $visionData['text'],
                    'faces' => $visionData['faces'],
                    'web_entities' => $visionData['web_entities'],
                    'similar_images' => $visionData['similar_images'],
                    'feature_vector' => $featureVector,
                    'processed' => true,
                    'processed_at' => now(),
                ]);
            }

            return [
                'vision_data' => $visionData,
                'feature_vector' => $featureVector,
            ];

        } catch (\Exception $e) {
            Log::error('Google Vision Analysis Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rechercher des produits similaires par image
     */
    public function searchSimilarProducts($imagePath, $limit = 10)
    {
        // Analyser l'image de recherche
        $analysisResult = $this->analyzeImage($imagePath);
        
        if (!$analysisResult) {
            return [];
        }

        $searchVector = $analysisResult['feature_vector'];
        $visionData = $analysisResult['vision_data'];

        // Récupérer tous les produits avec leurs données de vision
        $productVisionData = ProductVisionData::with('product')
            ->where('processed', true)
            ->get();

        $similarities = [];

        foreach ($productVisionData as $productVision) {
            if (!$productVision->product || $productVision->product->status !== 'active') {
                continue;
            }

            // Calculer la similarité
            $similarity = $this->calculateSimilarity(
                $searchVector,
                $productVision->feature_vector,
                $visionData,
                [
                    'labels' => $productVision->labels,
                    'objects' => $productVision->objects,
                    'colors' => $productVision->colors,
                    'web_entities' => $productVision->web_entities,
                ]
            );

            if ($similarity > 0.1) { // Seuil minimum de similarité
                $similarities[] = [
                    'product' => $productVision->product,
                    'similarity' => $similarity,
                    'vision_data' => $productVision,
                ];
            }
        }

        // Trier par similarité décroissante
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarities, 0, $limit);
    }

    /**
     * Extraire les labels de l'annotation
     */
    private function extractLabels($annotations)
    {
        $labels = [];
        foreach ($annotations->getLabelAnnotations() as $label) {
            $labels[] = [
                'description' => $label->getDescription(),
                'score' => $label->getScore(),
                'topicality' => $label->getTopicality(),
            ];
        }
        return $labels;
    }

    /**
     * Extraire les objets de l'annotation
     */
    private function extractObjects($annotations)
    {
        $objects = [];
        foreach ($annotations->getLocalizedObjectAnnotations() as $object) {
            $objects[] = [
                'name' => $object->getName(),
                'score' => $object->getScore(),
                'bounding_poly' => $this->extractBoundingPoly($object->getBoundingPoly()),
            ];
        }
        return $objects;
    }

    /**
     * Extraire les couleurs de l'annotation
     */
    private function extractColors($annotations)
    {
        $colors = [];
        if ($annotations->hasImagePropertiesAnnotation()) {
            $properties = $annotations->getImagePropertiesAnnotation();
            if ($properties->hasDominantColorsAnnotation()) {
                foreach ($properties->getDominantColorsAnnotation()->getColors() as $colorInfo) {
                    $color = $colorInfo->getColor();
                    $colors[] = [
                        'red' => $color->getRed(),
                        'green' => $color->getGreen(),
                        'blue' => $color->getBlue(),
                        'alpha' => $color->getAlpha() ?: 1.0,
                        'score' => $colorInfo->getScore(),
                        'pixel_fraction' => $colorInfo->getPixelFraction(),
                    ];
                }
            }
        }
        return $colors;
    }

    /**
     * Extraire le texte de l'annotation
     */
    private function extractText($annotations)
    {
        $textAnnotations = [];
        foreach ($annotations->getTextAnnotations() as $text) {
            $textAnnotations[] = [
                'description' => $text->getDescription(),
                'bounding_poly' => $this->extractBoundingPoly($text->getBoundingPoly()),
            ];
        }
        return $textAnnotations;
    }

    /**
     * Extraire les visages de l'annotation
     */
    private function extractFaces($annotations)
    {
        $faces = [];
        foreach ($annotations->getFaceAnnotations() as $face) {
            $faces[] = [
                'joy_likelihood' => $face->getJoyLikelihood(),
                'sorrow_likelihood' => $face->getSorrowLikelihood(),
                'anger_likelihood' => $face->getAngerLikelihood(),
                'surprise_likelihood' => $face->getSurpriseLikelihood(),
                'under_exposed_likelihood' => $face->getUnderExposedLikelihood(),
                'blurred_likelihood' => $face->getBlurredLikelihood(),
                'headwear_likelihood' => $face->getHeadwearLikelihood(),
                'bounding_poly' => $this->extractBoundingPoly($face->getBoundingPoly()),
            ];
        }
        return $faces;
    }

    /**
     * Extraire les entités web de l'annotation
     */
    private function extractWebEntities($annotations)
    {
        $webEntities = [];
        if ($annotations->hasWebDetection()) {
            $webDetection = $annotations->getWebDetection();
            foreach ($webDetection->getWebEntities() as $entity) {
                $webEntities[] = [
                    'entity_id' => $entity->getEntityId(),
                    'score' => $entity->getScore(),
                    'description' => $entity->getDescription(),
                ];
            }
        }
        return $webEntities;
    }

    /**
     * Extraire les images similaires de l'annotation
     */
    private function extractSimilarImages($annotations)
    {
        $similarImages = [];
        if ($annotations->hasWebDetection()) {
            $webDetection = $annotations->getWebDetection();
            foreach ($webDetection->getFullMatchingImages() as $image) {
                $similarImages[] = [
                    'url' => $image->getUrl(),
                    'type' => 'full_match',
                ];
            }
            foreach ($webDetection->getPartialMatchingImages() as $image) {
                $similarImages[] = [
                    'url' => $image->getUrl(),
                    'type' => 'partial_match',
                ];
            }
        }
        return $similarImages;
    }

    /**
     * Extraire le polygone de délimitation
     */
    private function extractBoundingPoly($boundingPoly)
    {
        $vertices = [];
        if ($boundingPoly) {
            foreach ($boundingPoly->getVertices() as $vertex) {
                $vertices[] = [
                    'x' => $vertex->getX(),
                    'y' => $vertex->getY(),
                ];
            }
        }
        return $vertices;
    }

    /**
     * Calculer le vecteur de caractéristiques
     */
    private function calculateFeatureVector($visionData)
    {
        $features = [];

        // Features basées sur les labels
        foreach ($visionData['labels'] as $label) {
            $features['label_' . strtolower(str_replace(' ', '_', $label['description']))] = $label['score'];
        }

        // Features basées sur les objets
        foreach ($visionData['objects'] as $object) {
            $features['object_' . strtolower(str_replace(' ', '_', $object['name']))] = $object['score'];
        }

        // Features basées sur les couleurs dominantes
        foreach (array_slice($visionData['colors'], 0, 5) as $i => $color) {
            $features['color_' . $i . '_red'] = $color['red'] / 255.0;
            $features['color_' . $i . '_green'] = $color['green'] / 255.0;
            $features['color_' . $i . '_blue'] = $color['blue'] / 255.0;
            $features['color_' . $i . '_fraction'] = $color['pixel_fraction'];
        }

        // Features basées sur les entités web
        foreach (array_slice($visionData['web_entities'], 0, 10) as $entity) {
            if ($entity['description']) {
                $features['web_' . strtolower(str_replace(' ', '_', $entity['description']))] = $entity['score'];
            }
        }

        return json_encode($features);
    }

    /**
     * Calculer la similarité entre deux images
     */
    private function calculateSimilarity($vector1, $vector2, $visionData1, $visionData2)
    {
        $features1 = json_decode($vector1, true);
        $features2 = json_decode($vector2, true);

        if (!$features1 || !$features2) {
            return 0;
        }

        // Similarité cosinus sur les vecteurs de features
        $cosineSimilarity = $this->cosineSimilarity($features1, $features2);

        // Similarité basée sur les labels communs
        $labelSimilarity = $this->labelSimilarity($visionData1['labels'], $visionData2['labels']);

        // Similarité basée sur les objets communs
        $objectSimilarity = $this->objectSimilarity($visionData1['objects'], $visionData2['objects']);

        // Similarité basée sur les couleurs
        $colorSimilarity = $this->colorSimilarity($visionData1['colors'], $visionData2['colors']);

        // Score final pondéré
        return (
            $cosineSimilarity * 0.4 +
            $labelSimilarity * 0.3 +
            $objectSimilarity * 0.2 +
            $colorSimilarity * 0.1
        );
    }

    /**
     * Calculer la similarité cosinus entre deux vecteurs
     */
    private function cosineSimilarity($vector1, $vector2)
    {
        $allKeys = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
        
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($allKeys as $key) {
            $val1 = $vector1[$key] ?? 0;
            $val2 = $vector2[$key] ?? 0;

            $dotProduct += $val1 * $val2;
            $magnitude1 += $val1 * $val1;
            $magnitude2 += $val2 * $val2;
        }

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($magnitude1) * sqrt($magnitude2));
    }

    /**
     * Calculer la similarité entre les labels
     */
    private function labelSimilarity($labels1, $labels2)
    {
        if (empty($labels1) || empty($labels2)) {
            return 0;
        }

        $labelSet1 = array_map(function($label) {
            return strtolower($label['description']);
        }, $labels1);

        $labelSet2 = array_map(function($label) {
            return strtolower($label['description']);
        }, $labels2);

        $intersection = count(array_intersect($labelSet1, $labelSet2));
        $union = count(array_unique(array_merge($labelSet1, $labelSet2)));

        return $union > 0 ? $intersection / $union : 0;
    }

    /**
     * Calculer la similarité entre les objets
     */
    private function objectSimilarity($objects1, $objects2)
    {
        if (empty($objects1) || empty($objects2)) {
            return 0;
        }

        $objectSet1 = array_map(function($object) {
            return strtolower($object['name']);
        }, $objects1);

        $objectSet2 = array_map(function($object) {
            return strtolower($object['name']);
        }, $objects2);

        $intersection = count(array_intersect($objectSet1, $objectSet2));
        $union = count(array_unique(array_merge($objectSet1, $objectSet2)));

        return $union > 0 ? $intersection / $union : 0;
    }

    /**
     * Calculer la similarité entre les couleurs
     */
    private function colorSimilarity($colors1, $colors2)
    {
        if (empty($colors1) || empty($colors2)) {
            return 0;
        }

        // Prendre les 3 couleurs dominantes
        $colors1 = array_slice($colors1, 0, 3);
        $colors2 = array_slice($colors2, 0, 3);

        $totalSimilarity = 0;
        $comparisons = 0;

        foreach ($colors1 as $color1) {
            foreach ($colors2 as $color2) {
                $distance = sqrt(
                    pow($color1['red'] - $color2['red'], 2) +
                    pow($color1['green'] - $color2['green'], 2) +
                    pow($color1['blue'] - $color2['blue'], 2)
                );
                
                $similarity = 1 - ($distance / sqrt(3 * 255 * 255));
                $totalSimilarity += $similarity;
                $comparisons++;
            }
        }

        return $comparisons > 0 ? $totalSimilarity / $comparisons : 0;
    }

    /**
     * Générer des données de démonstration sans Google Vision API
     */
    private function generateDemoAnalysis($imagePath, $productId = null)
    {
        // Analyser le nom du fichier pour deviner le contenu
        $filename = basename($imagePath);
        
        // Données de démonstration basiques
        $demoLabels = [
            ['description' => 'Clothing', 'score' => 0.95, 'topicality' => 0.95],
            ['description' => 'Fashion', 'score' => 0.89, 'topicality' => 0.89],
            ['description' => 'Apparel', 'score' => 0.82, 'topicality' => 0.82],
        ];

        $demoObjects = [
            ['name' => 'Clothing', 'score' => 0.87, 'bounding_poly' => []],
        ];

        $demoColors = [
            ['red' => 120, 'green' => 80, 'blue' => 60, 'alpha' => 1.0, 'score' => 0.3, 'pixel_fraction' => 0.25],
            ['red' => 200, 'green' => 180, 'blue' => 160, 'alpha' => 1.0, 'score' => 0.25, 'pixel_fraction' => 0.20],
        ];

        $demoWebEntities = [
            ['entity_id' => '', 'score' => 0.8, 'description' => 'Fashion'],
            ['entity_id' => '', 'score' => 0.7, 'description' => 'Style'],
        ];

        $visionData = [
            'labels' => $demoLabels,
            'objects' => $demoObjects,
            'colors' => $demoColors,
            'text' => [],
            'faces' => [],
            'web_entities' => $demoWebEntities,
            'similar_images' => [],
        ];

        // Calculer le vecteur de caractéristiques
        $featureVector = $this->calculateFeatureVector($visionData);

        // Sauvegarder en base de données si un product_id est fourni
        if ($productId) {
            ProductVisionData::create([
                'product_id' => $productId,
                'image_path' => $imagePath,
                'labels' => $visionData['labels'],
                'objects' => $visionData['objects'],
                'colors' => $visionData['colors'],
                'text' => $visionData['text'],
                'faces' => $visionData['faces'],
                'web_entities' => $visionData['web_entities'],
                'similar_images' => $visionData['similar_images'],
                'feature_vector' => $featureVector,
                'processed' => true,
                'processed_at' => now(),
            ]);
        }

        return [
            'vision_data' => $visionData,
            'feature_vector' => $featureVector,
        ];
    }
}
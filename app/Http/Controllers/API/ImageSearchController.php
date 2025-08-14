<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Vision\GoogleVisionService;
use App\Models\Product;
use App\Models\ProductVisionData;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageSearchController extends Controller
{
    private $visionService;

    public function __construct(GoogleVisionService $visionService)
    {
        $this->visionService = $visionService;
    }

    /**
     * Rechercher des produits par image
     */
    public function searchByImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Sauvegarder temporairement l'image
            $image = $request->file('image');
            $tempFileName = 'temp_search_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $tempPath = storage_path('app/temp/' . $tempFileName);
            
            // Créer le dossier temp s'il n'existe pas
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            $image->move(dirname($tempPath), $tempFileName);

            // Rechercher des produits similaires
            $limit = $request->get('limit', 10);
            $similarProducts = $this->visionService->searchSimilarProducts($tempPath, $limit);

            // Nettoyer le fichier temporaire
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Formater les résultats
            $results = array_map(function ($result) {
                return [
                    'product' => new ProductResource($result['product']),
                    'similarity_score' => round($result['similarity'] * 100, 2),
                    'match_details' => [
                        'labels' => array_slice($result['vision_data']->labels ?? [], 0, 3),
                        'objects' => array_slice($result['vision_data']->objects ?? [], 0, 3),
                        'dominant_colors' => array_slice($result['vision_data']->colors ?? [], 0, 3),
                    ]
                ];
            }, $similarProducts);

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'total_found' => count($results),
                    'search_meta' => [
                        'limit' => $limit,
                        'algorithm_version' => '1.0',
                        'processing_time' => null, // Pourra être ajouté plus tard
                    ]
                ],
                'message' => count($results) > 0 
                    ? 'Found ' . count($results) . ' similar products'
                    : 'No similar products found'
            ]);

        } catch (\Exception $e) {
            // Nettoyer le fichier temporaire en cas d'erreur
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error processing image search',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Analyser une image pour extraire ses caractéristiques (pour debug/admin)
     */
    public function analyzeImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Sauvegarder temporairement l'image
            $image = $request->file('image');
            $tempFileName = 'temp_analyze_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $tempPath = storage_path('app/temp/' . $tempFileName);
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            $image->move(dirname($tempPath), $tempFileName);

            // Analyser l'image
            $analysis = $this->visionService->analyzeImage($tempPath);

            // Nettoyer le fichier temporaire
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            if (!$analysis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze image'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $analysis['vision_data'],
                'message' => 'Image analyzed successfully'
            ]);

        } catch (\Exception $e) {
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error analyzing image',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Traiter les images existantes des produits (commande artisan ou admin)
     */
    public function processExistingProducts(Request $request)
    {
        // Vérifier les permissions admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $limit = $request->get('limit', 50);
        $processed = 0;
        $errors = 0;

        // Récupérer les produits qui n'ont pas encore été traités
        $products = Product::whereDoesntHave('visionData')
            ->with('images')
            ->active()
            ->limit($limit)
            ->get();

        foreach ($products as $product) {
            try {
                $mainImage = $product->images->first();
                if (!$mainImage) {
                    continue;
                }

                $imagePath = storage_path('app/public/products/' . $mainImage->filename);
                if (!file_exists($imagePath)) {
                    continue;
                }

                $this->visionService->analyzeImage($imagePath, $product->id);
                $processed++;

            } catch (\Exception $e) {
                $errors++;
                \Log::error("Error processing product {$product->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'processed' => $processed,
                'errors' => $errors,
                'total_checked' => $products->count(),
            ],
            'message' => "Processed {$processed} products with {$errors} errors"
        ]);
    }

    /**
     * Obtenir les statistiques de la recherche par image
     */
    public function getStats()
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $stats = [
            'total_products_with_vision_data' => ProductVisionData::where('processed', true)->count(),
            'total_products' => Product::active()->count(),
            'processing_coverage' => 0,
            'average_labels_per_product' => 0,
            'average_objects_per_product' => 0,
            'most_common_labels' => [],
            'most_common_objects' => [],
        ];

        if ($stats['total_products'] > 0) {
            $stats['processing_coverage'] = round(
                ($stats['total_products_with_vision_data'] / $stats['total_products']) * 100, 
                2
            );
        }

        // Calculer les moyennes
        $visionData = ProductVisionData::where('processed', true)->get();
        if ($visionData->count() > 0) {
            $totalLabels = $visionData->sum(function ($data) {
                return count($data->labels ?? []);
            });
            $totalObjects = $visionData->sum(function ($data) {
                return count($data->objects ?? []);
            });

            $stats['average_labels_per_product'] = round($totalLabels / $visionData->count(), 1);
            $stats['average_objects_per_product'] = round($totalObjects / $visionData->count(), 1);

            // Labels les plus communs
            $allLabels = [];
            $allObjects = [];

            foreach ($visionData as $data) {
                foreach ($data->labels ?? [] as $label) {
                    $desc = $label['description'] ?? '';
                    if ($desc) {
                        $allLabels[$desc] = ($allLabels[$desc] ?? 0) + 1;
                    }
                }
                foreach ($data->objects ?? [] as $object) {
                    $name = $object['name'] ?? '';
                    if ($name) {
                        $allObjects[$name] = ($allObjects[$name] ?? 0) + 1;
                    }
                }
            }

            arsort($allLabels);
            arsort($allObjects);

            $stats['most_common_labels'] = array_slice($allLabels, 0, 10, true);
            $stats['most_common_objects'] = array_slice($allObjects, 0, 10, true);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Stats retrieved successfully'
        ]);
    }
}

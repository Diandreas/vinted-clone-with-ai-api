<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children'])
            ->whereNull('parent_id') // root categories
            ->active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

        public function show(Category $category)
    {
        $category->load(['children', 'parent', 'products' => function($query) {
            $query->active()->latest()->limit(20);
        }]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'products_count' => $category->products_count,
                'stats' => [
                    'total_products' => $category->products()->active()->count(),
                    'subcategories_count' => $category->children()->count(),
                ]
            ]
        ]);
    }
}

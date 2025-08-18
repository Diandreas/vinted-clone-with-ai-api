<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, products, users, brands
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        $sort = $request->get('sort', 'relevance');
        
        if (empty($query) && $type === 'all') {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'users' => [],
                    'brands' => [],
                    'categories' => [],
                    'total' => 0
                ]
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'products') {
            $productsQuery = Product::query()
                ->with(['user', 'images', 'category', 'brand', 'condition'])
                ->active();

            // Apply text search if query exists
            if (!empty($query)) {
                $productsQuery->where(function (Builder $q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('tags', 'like', "%{$query}%");
                });
            }

            // Apply filters
            $productsQuery = $this->applyProductFilters($productsQuery, $request);

            // Apply sorting
            $productsQuery = $this->applyProductSorting($productsQuery, $sort);

            // Get total count for pagination
            $total = $productsQuery->count();

            // Apply pagination
            $products = $productsQuery->skip(($page - 1) * $perPage)
                                    ->take($perPage)
                                    ->get();

            // Use ProductResource to format the response
            $results['products'] = ProductResource::collection($products);
            $results['total'] = $total;
        }

        if ($type === 'all' || $type === 'users') {
            // Fallback to DB search when Scout/Algolia is disabled
            $results['users'] = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%");
                })
                ->take(10)
                ->get();
        }

        if ($type === 'all' || $type === 'brands') {
            $results['brands'] = Brand::where('name', 'like', "%{$query}%")
                ->active()
                ->take(10)
                ->get();
        }

        if ($type === 'all' || $type === 'categories') {
            $results['categories'] = Category::where('name', 'like', "%{$query}%")
                ->active()
                ->take(10)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'query' => $query,
            'type' => $type,
            'page' => $page,
            'per_page' => $perPage,
            'sort' => $sort
        ]);
    }

    /**
     * Apply product filters to the query
     */
    private function applyProductFilters(Builder $query, Request $request): Builder
    {
        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Price filter
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        // Condition filter
        if ($request->has('condition') && $request->condition) {
            $query->where('condition_id', function ($subQuery) use ($request) {
                $subQuery->select('id')
                        ->from('conditions')
                        ->where('slug', $request->condition)
                        ->first();
            });
        }

        // Location filter
        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Size filter
        if ($request->has('size') && $request->size) {
            $query->where('size', 'like', "%{$request->size}%");
        }

        // Color filter
        if ($request->has('color') && $request->color) {
            $query->where('color', 'like', "%{$request->color}%");
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Apply sorting to the products query
     */
    private function applyProductSorting(Builder $query, string $sort): Builder
    {
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'date_new':
                $query->orderBy('created_at', 'desc');
                break;
            case 'date_old':
                $query->orderBy('created_at', 'asc');
                break;
            case 'relevance':
            default:
                // Default sorting by relevance (most recent first)
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Get popular search terms and product titles
        $suggestions = Product::where('title', 'like', "%{$query}%")
            ->active()
            ->groupBy('title')
            ->pluck('title')
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Get search statistics
     */
    public function stats(Request $request)
    {
        $totalProducts = Product::active()->count();
        $totalCategories = Category::active()->count();
        $totalBrands = Brand::active()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_brands' => $totalBrands,
            ]
        ]);
    }
}
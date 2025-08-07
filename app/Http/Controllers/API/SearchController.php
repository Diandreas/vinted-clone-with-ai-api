<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, products, users, brands
        
        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'users' => [],
                    'brands' => [],
                    'categories' => [],
                ]
            ]);
        }

        $results = [];

        if ($type === 'all' || $type === 'products') {
            $results['products'] = Product::search($query)
                ->with(['user', 'images', 'category', 'brand'])
                ->active()
                ->take(20)
                ->get();
        }

        if ($type === 'all' || $type === 'users') {
            $results['users'] = User::search($query)
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
            'type' => $type
        ]);
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
}
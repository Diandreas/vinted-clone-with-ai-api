<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'in:products,users,all',
        ]);

        $query = $request->query;
        $type = isset($request->type) ? $request->type : 'all';
        $results = [];

        if ($type === 'products' || $type === 'all') {
            $products = Product::search($query)
                ->take(20)
                ->get()
                ->load(['user', 'images', 'category']);

            $results['products'] = $products;
        }

        if ($type === 'users' || $type === 'all') {
            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->active()
                ->take(20)
                ->get();

            $results['users'] = $users;
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function suggestions(Request $request)
    {
        $suggestions = [
            'trending' => ['sneakers', 'vintage', 'designer'],
            'categories' => ['clothing', 'shoes', 'accessories'],
            'brands' => ['nike', 'adidas', 'zara'],
        ];

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}

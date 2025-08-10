<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['user', 'category', 'brand', 'condition', 'images']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $products = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }
    
    public function pending()
    {
        $products = Product::with(['user', 'category', 'brand', 'condition', 'images'])
                          ->where('status', 'draft')
                          ->latest()
                          ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }
    
    public function approve(Product $product)
    {
        $product->update(['status' => 'active']);
        
        return response()->json([
            'success' => true,
            'message' => 'Produit approuvé avec succès'
        ]);
    }
    
    public function reject(Product $product)
    {
        $product->update(['status' => 'removed']);
        
        return response()->json([
            'success' => true,
            'message' => 'Produit rejeté avec succès'
        ]);
    }
    
    public function feature(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        return response()->json([
            'success' => true,
            'message' => $product->is_featured ? 'Produit mis en avant' : 'Produit retiré des mises en avant'
        ]);
    }
    
    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }
}
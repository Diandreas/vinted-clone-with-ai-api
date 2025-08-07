<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Live;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get products from followed users
        $followingIds = $user ? $user->following()->pluck('following_id') : [];
        
        $products = Product::with(['user', 'images', 'category'])
            ->when($user && $followingIds->isNotEmpty(), function($query) use ($followingIds) {
                $query->whereIn('user_id', $followingIds);
            })
            ->active()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function following(Request $request)
    {
        $user = Auth::user();
        $followingIds = $user->following()->pluck('following_id');
        
        if ($followingIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'total' => 0
                ]
            ]);
        }

        $products = Product::with(['user', 'images', 'category'])
            ->whereIn('user_id', $followingIds)
            ->active()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function recommended(Request $request)
    {
        // Get trending/featured products
        $products = Product::with(['user', 'images', 'category'])
            ->active()
            ->trending() // Uses the trending scope from Product model
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function explore()
    {
        $data = [
            'trending_products' => Product::with(['user', 'images'])
                ->active()
                ->trending()
                ->limit(10)
                ->get(),
            
            'featured_products' => Product::with(['user', 'images'])
                ->active()
                ->featured()
                ->limit(10)
                ->get(),
            
            'live_streams' => Live::with(['user'])
                ->live()
                ->trending()
                ->limit(5)
                ->get(),
            
            'recent_stories' => Story::with(['user'])
                ->where('expires_at', '>', now())
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function refresh()
    {
        // Trigger feed refresh (could involve background jobs)
        return response()->json([
            'success' => true,
            'message' => 'Feed refreshed successfully'
        ]);
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Story;
use App\Models\Live;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id; // Inclure ses propres posts

        // Products des personnes suivies + recommandations
        $products = Product::with(['user', 'images', 'category', 'brand'])
            ->where(function($query) use ($followingIds) {
                $query->whereIn('user_id', $followingIds)
                    ->orWhere('is_featured', true);
            })
            ->active()
            ->published()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stories actives
        $stories = Story::with('user')
            ->whereIn('user_id', $followingIds)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        // Lives en cours
        $lives = Live::with('user')
            ->whereIn('user_id', $followingIds)
            ->live()
            ->orderBy('viewers_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'stories' => $stories,
                'lives' => $lives,
            ]
        ]);
    }

    public function explore(Request $request)
    {
        // Produits populaires et tendances
        $products = Product::with(['user', 'images', 'category'])
            ->active()
            ->published()
            ->where('likes_count', '>', 10)
            ->orWhere('is_featured', true)
            ->orderBy('likes_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}

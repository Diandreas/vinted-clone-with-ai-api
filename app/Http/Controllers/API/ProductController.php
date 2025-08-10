<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessProductImages;
use App\Jobs\IndexProductForSearch;

class ProductController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['user', 'category', 'brand', 'condition', 'mainImage'])
                        ->active();

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->condition_id) {
            $query->where('condition_id', $request->condition_id);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->size) {
            $query->where('size', $request->size);
        }

        if ($request->color) {
            $query->where('color', 'like', '%' . $request->color . '%');
        }

        if ($request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('likes_count', 'desc')
                      ->orderBy('views_count', 'desc');
                break;
            case 'recent':
                $query->latest();
                break;
            default:
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('is_boosted', 'desc')
                      ->latest();
        }

        $products = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0.01',
            'original_price' => 'nullable|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition_id' => 'required|exists:conditions,id',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'is_negotiable' => 'boolean',
            'minimum_offer' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'measurements' => 'nullable|array',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $product = Product::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'condition_id' => $request->condition_id,
            'size' => $request->size,
            'color' => $request->color,
            'material' => $request->material,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'location' => $request->location,
            'is_negotiable' => $request->is_negotiable ?? false,
            'minimum_offer' => $request->minimum_offer,
            'tags' => $request->tags,
            'measurements' => $request->measurements,
            'status' => Product::STATUS_ACTIVE,
        ]);

        // Process images synchronously to avoid serialization of UploadedFile in queue
        if ($request->hasFile('images')) {
            $job = new ProcessProductImages($product, $request->file('images'));
            $job->handle();
        }

        // Index for search
        IndexProductForSearch::dispatch($product);

        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'brand', 'condition']),
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product, Request $request)
    {
        $product->load([
            'user',
            'category', 
            'brand', 
            'condition', 
            'images',
            'comments.user'
        ]);

        // Record view
        if ($user = Auth::user()) {
            $product->recordView($user, $request->ip());
        } else {
            $product->recordView(null, $request->ip());
        }

        // Add user-specific data if authenticated
        if ($user = Auth::user()) {
            $product->is_liked = $product->isLikedBy($user);
            $product->is_favorited = $product->isFavoritedBy($user);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0.01',
            'original_price' => 'nullable|numeric|min:0.01',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition_id' => 'sometimes|exists:conditions,id',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'is_negotiable' => 'boolean',
            'minimum_offer' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'measurements' => 'nullable|array',
            'status' => 'sometimes|in:draft,active,removed',
        ]);

        $product->update($request->only([
            'title', 'description', 'price', 'original_price',
            'category_id', 'brand_id', 'condition_id',
            'size', 'color', 'material', 'shipping_cost',
            'location', 'is_negotiable', 'minimum_offer',
            'tags', 'measurements', 'status'
        ]));

        // Re-index for search if needed
        if ($product->wasChanged(['title', 'description', 'price', 'category_id', 'brand_id'])) {
            IndexProductForSearch::dispatch($product);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Like/unlike a product.
     */
    public function like(Product $product)
    {
        $user = Auth::user();
        $liked = $product->toggleLike($user);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $product->likes_count,
            'message' => $liked ? 'Product liked' : 'Product unliked'
        ]);
    }

    /**
     * Add/remove product from favorites.
     */
    public function favorite(Product $product)
    {
        $user = Auth::user();
        $favorited = $product->toggleFavorite($user);

        return response()->json([
            'success' => true,
            'favorited' => $favorited,
            'favorites_count' => $product->favorites_count,
            'message' => $favorited ? 'Product added to favorites' : 'Product removed from favorites'
        ]);
    }

    /**
     * Add a comment to the product.
     */
    public function addComment(Request $request, Product $product)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = $product->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $product->increment('comments_count');

        return response()->json([
            'success' => true,
            'data' => $comment->load('user'),
            'message' => 'Comment added successfully'
        ], 201);
    }

    /**
     * Get product comments.
     */
    public function getComments(Product $product, Request $request)
    {
        $comments = $product->comments()
                          ->with('user')
                          ->latest()
                          ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Boost a product.
     */
    public function boost(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'hours' => 'required|integer|min:1|max:168', // Max 1 week
        ]);

        $product->boost($request->hours);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product boosted successfully'
        ]);
    }

    /**
     * Share a product.
     */
    public function share(Product $product)
    {
        return response()->json([
            'success' => true,
            'share_url' => route('products.show', $product),
            'message' => 'Share URL generated'
        ]);
    }

    /**
     * Get user's products.
     */
    public function myProducts(Request $request)
    {
        $products = Auth::user()->products()
                        ->with(['user', 'category', 'brand', 'condition'])
                        ->latest()
                        ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get user's favorites.
     */
    public function myFavorites(Request $request)
    {
        $favorites = Auth::user()->favoriteProducts()
                         ->with(['user', 'category', 'brand', 'condition', 'mainImage'])
                         ->active()
                         ->latest('favorites.created_at')
                         ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Get user's liked products.
     */
    public function myLikes(Request $request)
    {
        $likes = Auth::user()->likedProducts()
                     ->with(['user', 'category', 'brand', 'condition', 'mainImage'])
                     ->active()
                     ->latest('product_likes.created_at')
                     ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $likes
        ]);
    }

    /**
     * Get draft products.
     */
    public function draft(Request $request)
    {
        $products = Auth::user()->products()
                        ->with(['category', 'brand', 'condition', 'mainImage'])
                        ->where('status', Product::STATUS_DRAFT)
                        ->latest()
                        ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get sold products.
     */
    public function sold(Request $request)
    {
        $products = Auth::user()->products()
                        ->with(['category', 'brand', 'condition', 'mainImage'])
                        ->where('status', Product::STATUS_SOLD)
                        ->latest('sold_at')
                        ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get user's product statistics.
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total_products' => $user->products()->count(),
            'active_products' => $user->products()->where('status', 'active')->count(),
            'draft_products' => $user->products()->where('status', 'draft')->count(),
            'sold_products' => $user->products()->where('status', 'sold')->count(),
            'reserved_products' => $user->products()->where('status', 'reserved')->count(),
            'total_views' => $user->products()->sum('views_count'),
            'total_likes' => $user->products()->sum('likes_count'),
            'total_favorites' => $user->products()->sum('favorites_count'),
            'average_price' => $user->products()->where('status', 'active')->avg('price'),
            'total_value' => $user->products()->where('status', 'active')->sum('price'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get trending products.
     */
    public function trending(Request $request)
    {
        $products = Product::with(['user', 'category', 'brand', 'condition', 'mainImage'])
                          ->active()
                          ->trending($request->days ?? 7)
                          ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get similar products.
     */
    public function similar(Product $product, Request $request)
    {
        $similar = $product->getSimilarProducts($request->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => $similar
        ]);
    }
}
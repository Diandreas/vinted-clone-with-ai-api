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
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessProductImages;
use App\Jobs\IndexProductForSearch;
use App\Models\ProductAppointment;
use App\Notifications\FollowerOnlyProductPosted;
use App\Models\PlatformFee;
use App\Models\ProductFeeCharge;
use App\Http\Resources\ProductResource;
use App\Services\Payment\NotchPayService;

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

        // Spots filter
        if ($request->has('is_spot')) {
            $isSpot = filter_var($request->get('is_spot'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($isSpot)) {
                $query->where('is_spot', $isSpot);
            }
        }

        // Followers only filter (explicit)
        if ($request->has('followers_only')) {
            $followersOnly = filter_var($request->get('followers_only'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($followersOnly)) {
                $query->where('followers_only', $followersOnly);
            }
        }

        // Followers-only filtering for non-followers
        if (Auth::check()) {
            $followingIds = Auth::user()->following()->pluck('following_id');
            $query->where(function($q) use ($followingIds) {
                $q->where('followers_only', false)
                  ->orWhere(function($q2) use ($followingIds) {
                      $q2->where('followers_only', true)
                         ->whereIn('user_id', $followingIds);
                  })
                  ->orWhere('user_id', Auth::id());
            });
        } else {
            $query->where('followers_only', false);
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

        $products = $query->paginate($request->limit ?? $request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
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
            // Accept images and videos; enforce reasonable sizes
            'images.*' => 'file|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/webm|max:51200', // 50MB
            'followers_only' => 'nullable|boolean',
            'is_spot' => 'nullable|boolean',
            'spot_starts_at' => 'nullable|date',
            'spot_ends_at' => 'nullable|date|after:spot_starts_at',
        ]);

        // Check if listing fee is required
        $listingFee = PlatformFee::where('code', 'listing_fee')->where('active', true)->first();
        $initialStatus = $listingFee ? Product::STATUS_PENDING_PAYMENT : Product::STATUS_ACTIVE;
        
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
            'status' => $initialStatus,
            'followers_only' => (bool) ($request->followers_only ?? false),
            'is_spot' => (bool) ($request->is_spot ?? false),
            'spot_starts_at' => $request->spot_starts_at,
            'spot_ends_at' => $request->spot_ends_at,
        ]);

        // Process images synchronously to avoid serialization of UploadedFile in queue
        if ($request->hasFile('images')) {
            $job = new ProcessProductImages($product, $request->file('images'));
            $job->handle();
        }

        // Create listing fee charge (if required)
        if ($listingFee) {
            $amount = $listingFee->type === 'percentage'
                ? round(($listingFee->percentage / 100) * (float) $product->price, 2)
                : (float) $listingFee->amount;
            ProductFeeCharge::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'fee_id' => $listingFee->id,
                'amount' => $amount,
                'status' => 'pending',
            ]);
        }

        // Notify followers if followers-only
        if ($product->followers_only) {
            $seller = $product->user()->first();
            if ($seller) {
                $followers = $seller->followers()->get();
                foreach ($followers as $follower) {
                    $follower->notify(new FollowerOnlyProductPosted($product));
                }
            }
        }

        // Index for search
        IndexProductForSearch::dispatch($product);

        $product->load(['user', 'category', 'brand', 'condition', 'images']);
        $productData = $product->toArray();
        $productData['main_image'] = $product->mainImage?->url;
        $productData['images'] = $product->images->map(function($image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
                'order' => $image->order,
            ];
        });

        // Prepare response message based on status
        if ($product->status === Product::STATUS_PENDING_PAYMENT) {
            $message = '🚀 Produit créé avec succès ! Pour le publier et le rendre visible aux acheteurs, veuillez payer les frais de publication.';
        } else {
            $message = '✅ Produit créé et publié avec succès !';
        }
            
        $responseData = [
            'success' => true,
            'data' => $productData,
            'message' => $message,
        ];
        
        // Add payment info if required
        if ($product->status === Product::STATUS_PENDING_PAYMENT && $listingFee) {
            $amount = $listingFee->type === 'percentage'
                ? round(($listingFee->percentage / 100) * (float) $product->price, 2)
                : (float) $listingFee->amount;
                
            $responseData['payment_required'] = [
                'amount' => $amount,
                'currency' => 'XAF',
                'fee_type' => 'listing_fee',
                'product_status' => 'pending_payment',
                'instructions' => [
                    'title' => '💳 Paiement requis pour publication',
                    'message' => "Votre produit '{$product->title}' a été créé mais nécessite un paiement de {$amount} XAF pour être publié.",
                    'steps' => [
                        '1️⃣ Cliquez sur "Payer maintenant" ci-dessous',
                        '2️⃣ Choisissez votre mode de paiement (Mobile Money, Carte bancaire...)',
                        '3️⃣ Confirmez le paiement de ' . number_format($amount, 0, ',', ' ') . ' FCFA',
                        '4️⃣ Votre produit sera automatiquement publié après paiement'
                    ],
                    'actions' => [
                        'pay_now_url' => url("/api/v1/publishing/calculate-single-fee"),
                        'view_pending_products_url' => url("/api/v1/products/pending-payment"),
                        'product_details_url' => url("/api/v1/products/{$product->id}")
                    ]
                ]
            ];
        }
        
        return response()->json($responseData, 201);
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

        // Visibility gate for followers-only show
        if ($product->followers_only) {
            $viewer = Auth::user();
            if (!$viewer || ($viewer->id !== $product->user_id && !$viewer->isFollowing($product->user))) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product is only visible to followers'
                ], 403);
            }
        }

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

        // Ajouter les accesseurs manuellement
        $productData = $product->toArray();
        $productData['main_image_url'] = $product->main_image_url;
        $productData['image_urls'] = $product->image_urls;
        
        // Ajouter les URLs des images individuelles
        if ($product->images) {
            $productData['images'] = $product->images->map(function($image) {
                $imageData = $image->toArray();
                $imageData['url'] = $image->url;
                $imageData['thumbnail_url'] = $image->thumbnail_url;
                return $imageData;
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => $productData
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

        // Vérifier si le produit peut encore être modifié (30 minutes après création)
        $createdAt = $product->created_at;
        $now = now();
        $diffInMinutes = $createdAt->diffInMinutes($now);
        
        if ($diffInMinutes > 30) {
            return response()->json([
                'success' => false,
                'message' => 'Product can only be edited within 30 minutes of creation',
                'created_at' => $createdAt,
                'current_time' => $now,
                'minutes_elapsed' => $diffInMinutes
            ], 422);
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
            'followers_only' => 'nullable|boolean',
            'is_spot' => 'nullable|boolean',
            'spot_starts_at' => 'nullable|date',
            'spot_ends_at' => 'nullable|date|after:spot_starts_at',
        ]);

        $product->update($request->only([
            'title', 'description', 'price', 'original_price',
            'category_id', 'brand_id', 'condition_id',
            'size', 'color', 'material', 'shipping_cost',
            'location', 'is_negotiable', 'minimum_offer',
            'tags', 'measurements', 'status',
            'followers_only', 'is_spot', 'spot_starts_at', 'spot_ends_at'
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
        
        \Log::info('🔄 toggleLike appelé', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'likes_count_before' => $product->likes_count
        ]);
        
        $liked = $product->toggleLike($user);
        
        $product->refresh(); // Rafraîchir le modèle
        
        \Log::info('✅ toggleLike terminé', [
            'product_id' => $product->id,
            'liked' => $liked,
            'likes_count_after' => $product->likes_count
        ]);

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
        
        \Log::info('🔄 toggleFavorite appelé', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'favorites_count_before' => $product->favorites_count
        ]);
        
        $favorited = $product->toggleFavorite($user);
        
        $product->refresh(); // Rafraîchir le modèle
        
        \Log::info('✅ toggleFavorite terminé', [
            'product_id' => $product->id,
            'favorited' => $favorited,
            'favorites_count_after' => $product->favorites_count
        ]);

        return response()->json([
            'success' => true,
            'favorited' => $favorited,
            'favorites_count' => $product->favorites_count,
            'message' => $favorited ? 'Product added to favorites' : 'Product removed from favorites'
        ]);
    }

    /**
     * Get like status for the current user.
     */
    public function getLikeStatus(Product $product)
    {
        $user = Auth::user();
        $liked = $product->isLikedByUser;

        return response()->json([
            'success' => true,
            'liked' => $liked
        ]);
    }

    /**
     * Get favorite status for the current user.
     */
    public function getFavoriteStatus(Product $product)
    {
        $user = Auth::user();
        $favorited = $product->isFavoritedByUser;

        return response()->json([
            'success' => true,
            'favorited' => $favorited
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
     * Request an appointment to view the product.
     */
    public function requestAppointment(Request $request, Product $product)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment = ProductAppointment::create([
            'product_id' => $product->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $product->user_id,
            'scheduled_at' => $request->scheduled_at,
            'location' => $request->location,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment requested successfully'
        ], 201);
    }

    /**
     * Update appointment status by seller.
     */
    public function updateAppointmentStatus(Request $request, ProductAppointment $appointment)
    {
        if ($appointment->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:accepted,declined,cancelled,completed',
        ]);

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $appointment,
            'message' => 'Appointment status updated'
        ]);
    }

    /**
     * Get user's products.
     */
    public function myProducts(Request $request)
    {
        $products = Auth::user()->products()
                        ->with(['user', 'images', 'mainImage'])
                        ->latest()
                        ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
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
            'data' => ProductResource::collection($favorites)
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
            'data' => ProductResource::collection($likes)
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
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Get products pending payment.
     */
    public function pendingPayment(Request $request)
    {
        $products = Auth::user()->products()
                        ->with(['category', 'brand', 'condition', 'mainImage'])
                        ->where('status', Product::STATUS_PENDING_PAYMENT)
                        ->latest()
                        ->paginate($request->per_page ?? 20);

        // Calculate total fees pending
        $totalFeesPending = ProductFeeCharge::whereHas('product', function($query) {
                $query->where('user_id', Auth::id())
                      ->where('status', Product::STATUS_PENDING_PAYMENT);
            })
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'total_pending_products' => $products->total(),
                'total_fees_pending' => $totalFeesPending,
                'currency' => 'XAF',
                'instructions' => [
                    'title' => '📋 Vos produits en attente de publication',
                    'message' => 'Ces produits nécessitent un paiement pour être publiés et visibles aux acheteurs.',
                    'total_to_pay' => number_format($totalFeesPending, 0, ',', ' ') . ' FCFA',
                    'actions' => [
                        'pay_all_url' => url('/api/v1/publishing/create-exact-package'),
                        'individual_payment_info' => 'Cliquez sur chaque produit pour payer individuellement'
                    ]
                ]
            ]
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
            'data' => ProductResource::collection($products)
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
            'pending_payment_products' => $user->products()->where('status', 'pending_payment')->count(),
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
            'data' => ProductResource::collection($products)
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

    /**
     * Update product status.
     */
    public function updateStatus(Product $product, Request $request)
    {
        // Verify user owns the product
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:draft,pending_payment,active,sold,reserved,removed'
        ]);

        $newStatus = $request->status;

        // Business rules for status changes
        if ($newStatus === Product::STATUS_ACTIVE && $product->isPendingPayment()) {
            // Check if listing fee is paid before allowing activation
            $feeCharge = ProductFeeCharge::where('product_id', $product->id)
                ->whereHas('fee', function($query) {
                    $query->where('code', 'listing_fee');
                })
                ->first();

            if ($feeCharge && $feeCharge->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate product: listing fee must be paid first',
                    'payment_required' => [
                        'amount' => $feeCharge->amount,
                        'currency' => $feeCharge->currency,
                        'status' => $feeCharge->status
                    ]
                ], 400);
            }
        }

        // Special handling for marking as sold
        if ($newStatus === Product::STATUS_SOLD) {
            $product->markAsSold();
        } else {
            $product->update(['status' => $newStatus]);
        }

        // Handle notifications and indexing based on new status
        if ($newStatus === Product::STATUS_ACTIVE && $product->wasChanged('status')) {
            // Notify followers if followers-only and newly activated
            if ($product->followers_only) {
                $seller = $product->user()->first();
                if ($seller) {
                    $followers = $seller->followers()->get();
                    foreach ($followers as $follower) {
                        $follower->notify(new FollowerOnlyProductPosted($product));
                    }
                }
            }
            
            // Index for search when activated
            IndexProductForSearch::dispatch($product);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully',
            'data' => [
                'id' => $product->id,
                'status' => $product->status,
                'previous_status' => $product->getOriginal('status')
            ]
        ]);
    }

    /**
     * Get payment details for a pending product.
     */
    public function getPaymentDetails(Product $product)
    {
        // Verify user owns the product
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if product is pending payment
        if (!$product->isPendingPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not pending payment'
            ], 400);
        }

        // Get fee charge details
        $feeCharge = ProductFeeCharge::where('product_id', $product->id)
            ->with('fee')
            ->whereHas('fee', function($query) {
                $query->where('code', 'listing_fee');
            })
            ->first();

        if (!$feeCharge) {
            return response()->json([
                'success' => false,
                'message' => 'No fee charge found for this product'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'status' => $product->status,
                    'main_image' => $product->mainImage?->url,
                ],
                'payment' => [
                    'amount' => $feeCharge->amount,
                    'currency' => $feeCharge->currency,
                    'fee_name' => $feeCharge->fee->name,
                    'status' => $feeCharge->status,
                    'formatted_amount' => number_format($feeCharge->amount, 0, ',', ' ') . ' FCFA',
                ],
                'instructions' => [
                    'title' => '💳 Paiement des frais de publication',
                    'message' => "Payez {$feeCharge->amount} FCFA pour publier votre produit '{$product->title}'",
                    'steps' => [
                        '1️⃣ Cliquez sur "Payer maintenant"',
                        '2️⃣ Choisissez Mobile Money ou Carte bancaire',
                        '3️⃣ Suivez les instructions de paiement',
                        '4️⃣ Votre produit sera publié automatiquement'
                    ],
                    'actions' => [
                        'pay_now_url' => url("/api/v1/publishing/calculate-single-fee"),
                        'back_to_pending_url' => url("/api/v1/products/pending-payment")
                    ]
                ]
            ]
        ]);
    }

    /**
     * Activate a product after listing fee payment.
     */
    public function activateAfterPayment(Product $product)
    {
        // Verify user owns the product
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if product is pending payment
        if (!$product->isPendingPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not pending payment'
            ], 400);
        }

        // Check if listing fee is paid
        $feeCharge = ProductFeeCharge::where('product_id', $product->id)
            ->whereHas('fee', function($query) {
                $query->where('code', 'listing_fee');
            })
            ->first();

        if (!$feeCharge || $feeCharge->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Listing fee must be paid before activation'
            ], 400);
        }

        // Activate the product
        $product->activateAfterPayment();

        // Notify followers if followers-only (now that it's active)
        if ($product->followers_only) {
            $seller = $product->user()->first();
            if ($seller) {
                $followers = $seller->followers()->get();
                foreach ($followers as $follower) {
                    $follower->notify(new FollowerOnlyProductPosted($product));
                }
            }
        }

        // Index for search (now that it's active)
        IndexProductForSearch::dispatch($product);

        return response()->json([
            'success' => true,
            'message' => 'Product activated successfully',
            'data' => [
                'id' => $product->id,
                'status' => $product->status,
            ]
        ]);
    }

    /**
     * Activate all user's products that are pending payment and have paid fees.
     */
    public function activateAllPendingProducts(Request $request)
    {
        $user = Auth::user();
        
        // Get all pending products for this user
        $pendingProducts = $user->products()
            ->where('status', Product::STATUS_PENDING_PAYMENT)
            ->get();

        if ($pendingProducts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products pending payment found'
            ], 404);
        }

        $activated = [];
        $failed = [];
        $paymentRequired = [];

        foreach ($pendingProducts as $product) {
            // Check if listing fee is paid for this product
            $feeCharge = ProductFeeCharge::where('product_id', $product->id)
                ->whereHas('fee', function($query) {
                    $query->where('code', 'listing_fee');
                })
                ->first();

            if (!$feeCharge) {
                $failed[] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'reason' => 'No fee charge found'
                ];
                continue;
            }

            if ($feeCharge->status !== 'paid') {
                $paymentRequired[] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'amount' => $feeCharge->amount,
                    'currency' => $feeCharge->currency,
                    'fee_status' => $feeCharge->status
                ];
                continue;
            }

            // Activate the product
            try {
                $product->activateAfterPayment();
                
                // Notify followers if followers-only
                if ($product->followers_only) {
                    $seller = $product->user()->first();
                    if ($seller) {
                        $followers = $seller->followers()->get();
                        foreach ($followers as $follower) {
                            $follower->notify(new FollowerOnlyProductPosted($product));
                        }
                    }
                }

                // Index for search
                IndexProductForSearch::dispatch($product);

                $activated[] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'status' => $product->status
                ];

            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'reason' => $e->getMessage()
                ];
            }
        }

        $response = [
            'success' => true,
            'message' => count($activated) > 0 
                ? count($activated) . ' produit(s) activé(s) avec succès'
                : 'Aucun produit n\'a pu être activé',
            'summary' => [
                'total_pending' => $pendingProducts->count(),
                'activated' => count($activated),
                'payment_required' => count($paymentRequired),
                'failed' => count($failed)
            ],
            'results' => [
                'activated' => $activated,
                'payment_required' => $paymentRequired,
                'failed' => $failed
            ]
        ];

        // Add instructions if there are products requiring payment
        if (!empty($paymentRequired)) {
            $totalToPay = collect($paymentRequired)->sum('amount');
            $response['payment_instructions'] = [
                'title' => '💳 Paiement requis pour certains produits',
                'message' => count($paymentRequired) . ' produit(s) nécessitent encore un paiement',
                'total_amount' => $totalToPay,
                'currency' => 'XAF',
                'formatted_total' => number_format($totalToPay, 0, ',', ' ') . ' FCFA',
                'actions' => [
                    'pay_all_url' => url('/api/v1/publishing/create-exact-package'),
                    'view_pending_url' => url('/api/v1/products/pending-payment')
                ]
            ];
        }

        return response()->json($response);
    }

    /**
     * Create bulk payment gateway for all pending products via Lygos.
     */
    public function createBulkPayment(Request $request)
    {
        $user = Auth::user();
        
        // Get all pending products for this user
        $pendingProducts = $user->products()
            ->where('status', Product::STATUS_PENDING_PAYMENT)
            ->get();

        if ($pendingProducts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun produit en attente de paiement trouvé'
            ], 404);
        }

        // Calculate total amount to pay using proper fee calculation
        $totalAmount = 0;
        $productsRequiringPayment = [];
        $feeService = new \App\Services\ProductPublishingFeeService();
        
        foreach ($pendingProducts as $product) {
            $feeCharge = ProductFeeCharge::where('product_id', $product->id)
                ->whereHas('fee', function($query) {
                    $query->where('code', 'listing_fee');
                })
                ->where('status', 'pending')
                ->first();
                
            if ($feeCharge) {
                // Use proper fee calculation instead of stored amount (which might be outdated)
                $properFeeAmount = $feeService->calculateSingleProductFee((float) $product->price);
                
                $totalAmount += $properFeeAmount;
                $productsRequiringPayment[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'fee_charge_id' => $feeCharge->id,
                    'amount' => $properFeeAmount, // Use recalculated amount
                    'product_price' => $product->price
                ];
            }
        }

        if (empty($productsRequiringPayment)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_required' => false,
                    'message' => 'Tous les produits peuvent être activés gratuitement'
                ]
            ]);
        }

        try {
            // Check if NotchPay is configured
            if (!config('services.notchpay.public_key')) {
                return response()->json([
                    'success' => false,
                    'message' => 'NotchPay payment service is not configured. Please add NOTCHPAY_PUBLIC_KEY and NOTCHPAY_SECRET_KEY to your .env file.',
                    'debug_info' => [
                        'notchpay_public_key_set' => !empty(config('services.notchpay.public_key')),
                        'notchpay_base_url' => config('services.notchpay.base_url'),
                        'total_amount' => $totalAmount,
                        'products_count' => count($productsRequiringPayment)
                    ]
                ], 400);
            }

            // Production mode with NotchPay
            $notchPayService = app(NotchPayService::class);
            
            $reference = 'bulk_activation_' . $user->id . '_' . time();
            $paymentData = [
                'email' => $user->email,
                'amount' => max(1, (int) round($totalAmount)), // Ensure amount is at least 1 FCFA and properly rounded
                'currency' => config('services.notchpay.currency', 'XAF'),
                'reference' => $reference,
                'description' => 'Paiement pour activation de ' . count($productsRequiringPayment) . ' produit(s)',
                'callback' => url('/api/v1/products/bulk-payment-callback'),
            ];

            $notchPayResponse = $notchPayService->initializePayment($paymentData);

            // Check if payment initialization was successful
            // NotchPay returns different response structures, check for success indicators
            $isSuccessful = false;
            $paymentUrl = null;
            
            if (isset($notchPayResponse['authorization_url'])) {
                $isSuccessful = true;
                $paymentUrl = $notchPayResponse['authorization_url'];
            } elseif (isset($notchPayResponse['redirect_url'])) {
                $isSuccessful = true;
                $paymentUrl = $notchPayResponse['redirect_url'];
            } elseif (isset($notchPayResponse['status']) && $notchPayResponse['status'] === 'success') {
                $isSuccessful = true;
                $paymentUrl = $notchPayResponse['data']['authorization_url'] ?? null;
            }
            
            if (!$isSuccessful || !$paymentUrl) {
                throw new \Exception('NotchPay payment initialization failed: ' . json_encode($notchPayResponse));
            }

            // Store payment reference for callback handling
            $cacheKey = 'bulk_payment_' . $reference;
            cache()->put(
                $cacheKey, 
                [
                    'user_id' => $user->id,
                    'products' => $productsRequiringPayment,
                    'total_amount' => $totalAmount,
                    'notchpay_reference' => $reference
                ],
                now()->addHours(24) // Cache for 24 hours
            );
            
            // Log cache storage
            Log::info('Bulk payment cache stored', [
                'cache_key' => $cacheKey,
                'reference' => $reference,
                'user_id' => $user->id,
                'products_count' => count($productsRequiringPayment),
                'total_amount' => $totalAmount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_required' => true,
                    'total_amount' => $totalAmount,
                    'formatted_amount' => number_format($totalAmount, 0, ',', ' ') . ' FCFA',
                    'product_count' => count($productsRequiringPayment),
                    'notchpay_payment_link' => $paymentUrl,
                    'notchpay_reference' => $reference,
                    'products' => $productsRequiringPayment
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create bulk payment gateway', [
                'user_id' => $user->id,
                'products_count' => count($productsRequiringPayment),
                'total_amount' => $totalAmount,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk payment callback from NotchPay.
     */
    public function bulkPaymentCallback(Request $request)
    {
        try {
            // NotchPay sends reference in different fields, try them in order of priority
            $reference = $request->input('notchpay_trxref') ?? 
                        $request->query('notchpay_trxref') ?? 
                        $request->input('trxref') ?? 
                        $request->query('trxref') ?? 
                        $request->input('reference') ?? 
                        $request->query('reference');
            
            if (!$reference) {
                Log::error('NotchPay callback received without reference', $request->all());
                return response()->json([
                    'success' => false,
                    'message' => 'Référence de paiement manquante'
                ], 400);
            }
            
            // Log the reference being used
            Log::info('Bulk payment callback received', [
                'reference' => $reference,
                'all_params' => $request->all(),
                'query_params' => $request->query()
            ]);

            // Get payment data from cache
            $cacheKey = 'bulk_payment_' . $reference;
            $paymentData = cache()->get($cacheKey);

            if (!$paymentData) {
                Log::error('Payment data not found in cache', [
                    'reference' => $reference,
                    'cache_key' => $cacheKey,
                    'all_params' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Données de paiement non trouvées'
                ], 404);
            }
            
            Log::info('Payment data found in cache', [
                'cache_key' => $cacheKey,
                'reference' => $reference,
                'user_id' => $paymentData['user_id'] ?? 'unknown',
                'products_count' => count($paymentData['products'] ?? [])
            ]);

            // Verify payment with NotchPay
            // Use the actual NotchPay transaction reference, not our internal reference
            $notchpayTransactionRef = $request->input('reference') ?? $request->query('reference');
            
            if (!$notchpayTransactionRef) {
                Log::error('NotchPay transaction reference not found', $request->all());
                return response()->json([
                    'success' => false,
                    'message' => 'Référence de transaction NotchPay manquante'
                ], 400);
            }
            
            $notchPayService = app(NotchPayService::class);
            
            Log::info('Verifying payment with NotchPay', [
                'notchpay_transaction_ref' => $notchpayTransactionRef,
                'our_reference' => $reference
            ]);
            
            $paymentVerification = $notchPayService->verifyPayment($notchpayTransactionRef);
            
            Log::info('NotchPay verification response', [
                'verification' => $paymentVerification
            ]);

            // Check if payment is successful
            // In test mode, "expired" can also mean the payment was processed
            $transactionStatus = $paymentVerification['transaction']['status'] ?? '';
            $isSandbox = $paymentVerification['transaction']['sandbox'] ?? false;
            
            $isSuccessful = in_array($transactionStatus, ['complete', 'completed', 'success']) ||
                           ($isSandbox && in_array($transactionStatus, ['expired', 'pending']));
            
            Log::info('Payment status check', [
                'transaction_status' => $transactionStatus,
                'is_sandbox' => $isSandbox,
                'is_successful' => $isSuccessful
            ]);

            if ($isSuccessful) {
                // Mark all fee charges as paid
                $activatedProducts = [];
                foreach ($paymentData['products'] as $productData) {
                    try {
                        $feeCharge = ProductFeeCharge::where('product_id', $productData['product_id'])
                            ->whereHas('fee', function($query) {
                                $query->where('code', 'listing_fee');
                            })
                            ->where('status', 'pending')
                            ->first();
                        
                        if ($feeCharge) {
                            // Update fee charge with proper amount and mark as paid
                            $feeCharge->update([
                                'amount' => $productData['amount'], // Update with the actual amount paid
                                'status' => 'paid',
                                'paid_at' => now(),
                                'payment_method' => 'notchpay',
                                'meta' => [
                                    'reason' => 'bulk_payment',
                                    'notchpay_reference' => $reference,
                                    'payment_verification' => $paymentVerification,
                                    'original_amount' => $feeCharge->amount, // Keep original for audit
                                    'corrected_amount' => $productData['amount']
                                ]
                            ]);

                            // Activate the product using same logic as individual activation
                            $product = Product::find($productData['product_id']);
                            if ($product && $product->isPendingPayment()) {
                                $product->update([
                                    'status' => Product::STATUS_ACTIVE,
                                    'activated_at' => now(),
                                ]);
                                
                                // Index for search
                                IndexProductForSearch::dispatch($product);
                                
                                $activatedProducts[] = $product->title;
                                
                                Log::info('Product activated in bulk payment', [
                                    'product_id' => $product->id,
                                    'user_id' => $product->user_id,
                                    'product_name' => $product->title,
                                    'fee_charge_id' => $feeCharge->id,
                                    'bulk_reference' => $reference
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing product activation', [
                            'product_id' => $productData['product_id'],
                            'error' => $e->getMessage()
                        ]);
                        $activatedProducts[] = 'Erreur lors de l\'activation du produit ID: ' . $productData['product_id'];
                    }
                }

                // Clear cache
                cache()->forget('bulk_payment_' . $reference);

                // Store success message in session and redirect to payment result page
                $successCount = count(array_filter($activatedProducts, function($product) {
                    return !str_contains($product, 'Erreur');
                }));
                
                $successMessage = $successCount . ' produit(s) activé(s) avec succès ! Montant payé : ' . number_format($paymentData['total_amount'], 0, ',', ' ') . ' FCFA';
                
                // Store in both session and cache for reliability
                session()->flash('payment_success', $successMessage);
                session()->flash('activated_products', $activatedProducts);
                
                // Alternative: Use cache with a unique key
                $resultKey = 'payment_result_' . md5($reference . time());
                cache()->put($resultKey, [
                    'success' => true,
                    'message' => $successMessage,
                    'activated_products' => $activatedProducts,
                    'total_amount' => $paymentData['total_amount']
                ], now()->addMinutes(10));
                
                Log::info('Payment result data stored', [
                    'success_message' => $successMessage,
                    'activated_products' => $activatedProducts,
                    'session_id' => session()->getId(),
                    'cache_key' => $resultKey
                ]);
                
                return redirect()->route('payment.result', ['result' => $resultKey]);
            } else {
                Log::warning('NotchPay payment not successful', [
                    'reference' => $reference,
                    'verification' => $paymentVerification,
                    'transaction_status' => $transactionStatus
                ]);

                // Collect products that were supposed to be activated
                $affectedProducts = [];
                foreach ($paymentData['products'] as $productData) {
                    $product = Product::find($productData['product_id']);
                    if ($product) {
                        $affectedProducts[] = $product->title . ' (Statut: Non activé - Paiement ' . $transactionStatus . ')';
                    }
                }

                $errorMessage = 'Paiement ' . $transactionStatus . '. ';
                $errorMessage .= count($affectedProducts) . ' produit(s) n\'ont pas été activés.';
                
                if ($transactionStatus === 'canceled') {
                    $errorMessage .= ' Vous pouvez réessayer le paiement depuis votre profil.';
                }

                // Store error message and affected products info
                $errorKey = 'payment_error_' . md5($reference . time());
                cache()->put($errorKey, [
                    'success' => false,
                    'message' => $errorMessage,
                    'affected_products' => $affectedProducts,
                    'total_amount' => $paymentData['total_amount'],
                    'status' => $transactionStatus
                ], now()->addMinutes(10));

                session()->flash('payment_error', $errorMessage);
                session()->flash('affected_products', $affectedProducts);
                
                return redirect()->route('payment.result', ['result' => $errorKey]);
            }

        } catch (\Exception $e) {
            Log::error('Bulk payment callback error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            // Try to get payment data for error context
            $reference = $request->input('notchpay_trxref') ?? 
                        $request->query('notchpay_trxref') ?? 
                        $request->input('trxref') ?? 
                        $request->query('trxref') ?? 
                        $request->input('reference') ?? 
                        $request->query('reference');
            
            $affectedProducts = [];
            if ($reference) {
                $cacheKey = 'bulk_payment_' . $reference;
                $paymentData = cache()->get($cacheKey);
                
                if ($paymentData && isset($paymentData['products'])) {
                    foreach ($paymentData['products'] as $productData) {
                        $product = Product::find($productData['product_id']);
                        if ($product) {
                            $affectedProducts[] = $product->title . ' (Statut: Non activé - Erreur système)';
                        }
                    }
                }
            }

            $errorMessage = 'Erreur lors du traitement du paiement. ';
            if (!empty($affectedProducts)) {
                $errorMessage .= count($affectedProducts) . ' produit(s) n\'ont pas été activés. Veuillez réessayer.';
            }

            session()->flash('payment_error', $errorMessage);
            if (!empty($affectedProducts)) {
                session()->flash('affected_products', $affectedProducts);
            }
            
            return redirect()->route('payment.result');
        }
    }
}
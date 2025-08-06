<?php
// app/Http/Controllers/API/UserController.php
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['user', 'images', 'category', 'brand', 'condition'])
            ->active()
            ->published()
            ->when($request->category_id, function($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->brand_id, function($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            ->when($request->min_price, function($query, $minPrice) {
                $query->where('price', '>=', $minPrice);
            })
            ->when($request->max_price, function($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice);
            })
            ->when($request->condition_id, function($query, $conditionId) {
                $query->where('condition_id', $conditionId);
            })
            ->when($request->size, function($query, $size) {
                $query->where('size', $size);
            })
            ->when($request->color, function($query, $color) {
                $query->where('color', 'like', "%{$color}%");
            })
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy(isset($request->sort_by) ? $request->sort_by : 'created_at', isset($request->sort_order) ? $request->sort_order : 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition_id' => 'required|exists:conditions,id',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'material' => 'nullable|string',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'is_negotiable' => 'boolean',
            'status' => 'in:draft,active',
        ]);

        $product = Product::create([
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
            'user_id' => $request->user()->id,
            'is_negotiable' => $request->boolean('is_negotiable', true),
            'status' => isset($request->status) ? $request->status : 'active',
            'published_at' => $request->status === 'active' ? now() : null,
        ]);

        // Gestion des images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'url' => $path,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $product->load(['images', 'category', 'brand', 'condition']),
            'message' => 'Product created successfully'
        ], 201);
    }

    public function show(Request $request, Product $product)
    {
        // Enregistrer la vue
        ProductView::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'viewed_at' => now(),
        ]);

        $product->increment('views_count');

        $product->load([
            'user',
            'images',
            'category',
            'brand',
            'condition',
            'comments' => function($query) {
                $query->with('user')->latest();
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'condition_id' => 'sometimes|exists:conditions,id',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'material' => 'nullable|string',
            'is_negotiable' => 'boolean',
            'status' => 'in:draft,active,inactive',
        ]);

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $product->load(['images', 'category', 'brand', 'condition']),
            'message' => 'Product updated successfully'
        ]);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Supprimer les images
        foreach ($product->images as $image) {
            Storage::delete($image->url);
            $image->delete();
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function like(Request $request, Product $product)
    {
        $user = $request->user();

        if ($product->isLikedBy($user)) {
            $product->likes()->detach($user->id);
            $product->decrement('likes_count');
            $message = 'Product unliked';
        } else {
            $product->likes()->attach($user->id);
            $product->increment('likes_count');
            $message = 'Product liked';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_liked' => $product->isLikedBy($user)
        ]);
    }

    public function favorite(Request $request, Product $product)
    {
        $user = $request->user();

        if ($product->isFavoritedBy($user)) {
            $product->favorites()->detach($user->id);
            $product->decrement('favorites_count');
            $message = 'Product removed from favorites';
        } else {
            $product->favorites()->attach($user->id);
            $product->increment('favorites_count');
            $message = 'Product added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $product->isFavoritedBy($user)
        ]);
    }

    public function myProducts(Request $request)
    {
        $products = Product::with(['images', 'category', 'brand', 'condition'])
            ->where('user_id', $request->user()->id)
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function myFavorites(Request $request)
    {
        $products = $request->user()
            ->favorites()
            ->with(['user', 'images', 'category', 'brand', 'condition'])
            ->active()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}



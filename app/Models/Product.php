<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'condition_id',
        'title',
        'description',
        'price',
        'original_price',
        'size',
        'color',
        'material',
        'status',
        'is_featured',
        'is_boosted',
        'boosted_until',
        'views_count',
        'likes_count',
        'favorites_count',
        'comments_count',
        'sold_at',
        'tags',
        'measurements',
        'shipping_cost',
        'location',
        'is_negotiable',
        'minimum_offer',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'minimum_offer' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_boosted' => 'boolean',
            'is_negotiable' => 'boolean',
            'boosted_until' => 'datetime',
            'sold_at' => 'datetime',
            'tags' => 'array',
            'measurements' => 'array',
        ];
    }

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_RESERVED = 'reserved';
    const STATUS_REMOVED = 'removed';

    // Relations

    /**
     * Get the user that owns the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product's category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product's brand.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the product's condition.
     */
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    /**
     * Get the product's images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    /**
     * Get the product's first image.
     */
    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('order');
    }

    /**
     * Get the product's comments.
     */
    public function comments()
    {
        return $this->hasMany(ProductComment::class)->latest();
    }

    /**
     * Get the product's likes.
     */
    public function likes()
    {
        return $this->hasMany(ProductLike::class);
    }

    /**
     * Get the product's views.
     */
    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * Get the product's favorites.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the users who liked this product.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'product_likes');
    }

    /**
     * Get the users who favorited this product.
     */
    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    /**
     * Get the orders for this product.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reports for this product.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Scopes

    /**
     * Scope active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope available products (active and not sold).
     */
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_RESERVED]);
    }

    /**
     * Scope sold products.
     */
    public function scopeSold($query)
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    /**
     * Scope featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope boosted products.
     */
    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true)
                    ->where('boosted_until', '>', now());
    }

    /**
     * Scope products by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope products by brand.
     */
    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Scope products by price range.
     */
    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Scope products by size.
     */
    public function scopeBySize($query, $size)
    {
        return $query->where('size', $size);
    }

    /**
     * Scope trending products.
     */
    public function scopeTrending($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('views_count', 'desc')
                    ->orderBy('likes_count', 'desc');
    }

    /**
     * Scope recently added products.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                    ->latest();
    }

    /**
     * Scope popular products.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('likes_count', 'desc')
                    ->orderBy('views_count', 'desc')
                    ->orderBy('favorites_count', 'desc');
    }

    // Accessors & Mutators

    /**
     * Get the product's main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        $mainImage = $this->mainImage;
        return $mainImage 
            ? asset('storage/products/' . $mainImage->filename)
            : asset('images/product-placeholder.jpg');
    }

    /**
     * Get all product image URLs.
     */
    public function getImageUrlsAttribute()
    {
        return $this->images->map(function($image) {
            return asset('storage/products/' . $image->filename);
        });
    }

    /**
     * Get the product's discount percentage.
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return 0;
        }
        
        return round((($this->original_price - $this->price) / $this->original_price) * 100);
    }

    /**
     * Check if the product is available.
     */
    public function getIsAvailableAttribute()
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_RESERVED]);
    }

    /**
     * Check if the product is sold.
     */
    public function getIsSoldAttribute()
    {
        return $this->status === self::STATUS_SOLD;
    }

    /**
     * Check if the product is currently boosted.
     */
    public function getIsBoostedActiveAttribute()
    {
        return $this->is_boosted && $this->boosted_until && $this->boosted_until->isFuture();
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        $price = is_numeric($this->price) ? (float) $this->price : 0.0;
        return '€' . number_format($price, 2);
    }

    /**
     * Get formatted original price.
     */
    public function getFormattedOriginalPriceAttribute()
    {
        if ($this->original_price === null) {
            return null;
        }
        $original = is_numeric($this->original_price) ? (float) $this->original_price : 0.0;
        return '€' . number_format($original, 2);
    }

    /**
     * Get time since product was created.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods

    /**
     * Check if user has liked this product.
     */
    public function isLikedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user has favorited this product.
     */
    public function isFavoritedBy(User $user)
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    /**
     * Like/unlike the product by a user.
     */
    public function toggleLike(User $user)
    {
        $like = $this->likes()->where('user_id', $user->id)->first();
        
        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false; // unliked
        } else {
            $this->likes()->create(['user_id' => $user->id]);
            $this->increment('likes_count');
            return true; // liked
        }
    }

    /**
     * Add/remove product from user's favorites.
     */
    public function toggleFavorite(User $user)
    {
        $favorite = $this->favorites()->where('user_id', $user->id)->first();
        
        if ($favorite) {
            $favorite->delete();
            $this->decrement('favorites_count');
            return false; // removed from favorites
        } else {
            $this->favorites()->create(['user_id' => $user->id]);
            $this->increment('favorites_count');
            return true; // added to favorites
        }
    }

    /**
     * Record a view of this product.
     */
    public function recordView(User $user = null, $ipAddress = null)
    {
        // Don't record views from the product owner
        if ($user && $user->id === $this->user_id) {
            return;
        }

        $attributes = [
            'product_id' => $this->id,
            'viewed_at' => now(),
        ];

        if ($user) {
            $attributes['user_id'] = $user->id;
        }

        if ($ipAddress) {
            $attributes['ip_address'] = $ipAddress;
        }

        // Check if this user/IP already viewed this product recently (within 1 hour)
        $recentView = ProductView::where('product_id', $this->id)
            ->where(function($query) use ($user, $ipAddress) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } elseif ($ipAddress) {
                    $query->where('ip_address', $ipAddress);
                }
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if (!$recentView) {
            ProductView::create($attributes);
            $this->increment('views_count');
        }
    }

    /**
     * Mark product as sold.
     */
    public function markAsSold()
    {
        $this->update([
            'status' => self::STATUS_SOLD,
            'sold_at' => now(),
        ]);
    }

    /**
     * Boost the product for specified duration.
     */
    public function boost($hours = 24)
    {
        $this->update([
            'is_boosted' => true,
            'boosted_until' => now()->addHours($hours),
        ]);
    }

    /**
     * Get similar products.
     */
    public function getSimilarProducts($limit = 10)
    {
        return self::where('id', '!=', $this->id)
            ->where(function($query) {
                $query->where('category_id', $this->category_id)
                      ->orWhere('brand_id', $this->brand_id)
                      ->orWhere('size', $this->size);
            })
            ->active()
            ->limit($limit)
            ->get();
    }

    /**
     * Get searchable data for Scout.
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'size' => $this->size,
            'color' => $this->color,
            'material' => $this->material,
            'tags' => $this->tags ?? [],
            'category' => $this->category?->name,
            'brand' => $this->brand?->name,
            'condition' => $this->condition?->name,
            'user_name' => $this->user?->name,
            'location' => $this->location,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'is_featured' => $this->is_featured,
            'is_boosted' => $this->is_boosted_active,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class  User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'avatar',
        'cover_image',
        'phone',
        'location',
        'website',
        'date_of_birth',
        'gender',
        'is_verified',
        'is_live',
        'stripe_customer_id',
        'last_seen_at',
        'settings',
        'notification_settings',
        'privacy_settings',
        'wallet_balance_xaf',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
        'phone',
        'date_of_birth',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_seen_at' => 'datetime',
            'is_verified' => 'boolean',
            'is_live' => 'boolean',
            'settings' => 'array',
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
            'wallet_balance_xaf' => 'integer',
        ];
    }

    // Relations

    /**
     * Get the user's products.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the user's orders as buyer.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the user's sales.
     */
    public function sales()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * Get the user's favorites.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the user's favorite products.
     */
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }

    /**
     * Get the user's liked products.
     */
    public function likedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_likes');
    }

    /**
     * Get users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
                    ->withTimestamps();
    }

    /**
     * Get users that are following this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Get the user's lives.
     */
    public function lives()
    {
        return $this->hasMany(Live::class);
    }

    /**
     * Get the user's stories.
     */
    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get the user's conversations.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class);
    }

    /**
     * Get the user's sent messages.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the user's reviews as reviewer.
     */
    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get the user's reviews as reviewed.
     */
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    /**
     * Get the user's shipping addresses.
     */
    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    /**
     * Get the user's payment methods.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the user's reports made.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get the user's feed items.
     */
    public function feedItems()
    {
        return $this->hasMany(UserFeed::class);
    }

    // Scopes

    /**
     * Scope verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope users currently live.
     */
    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    /**
     * Scope recently active users.
     */
    public function scopeRecentlyActive($query, $minutes = 15)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes($minutes));
    }

    // Accessors & Mutators

    /**
     * Get the user's full avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        // Generate dynamic avatar with initials
        $initials = strtoupper(substr($this->first_name ?? $this->name, 0, 1) . substr($this->last_name ?? '', 0, 1));
        if (strlen($initials) < 2) {
            $initials = strtoupper(substr($this->name ?? 'U', 0, 2));
        }
        
        // Use a color based on user ID
        $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#06B6D4'];
        $color = $colors[$this->id % count($colors)];
        
        return "data:image/svg+xml;base64," . base64_encode(
            '<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">' .
            '<rect width="40" height="40" fill="' . $color . '"/>' .
            '<text x="50%" y="50%" text-anchor="middle" dy="0.35em" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">' .
            $initials .
            '</text>' .
            '</svg>'
        );
    }

    /**
     * Get the user's full cover image URL.
     */
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image
            ? asset('storage/covers/' . $this->cover_image)
            : null;
    }

    /**
     * Get the user's followers count.
     */
    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get the user's following count.
     */
    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

    /**
     * Get the user's products count.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get the user's average rating.
     */
    public function getAverageRatingAttribute()
    {
        return $this->receivedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get the user's reviews count.
     */
    public function getReviewsCountAttribute()
    {
        return $this->receivedReviews()->count();
    }

    // Methods

    /**
     * Check if user is following another user.
     */
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Follow a user.
     */
    public function follow(User $user)
    {
        if (!$this->isFollowing($user) && $this->id !== $user->id) {
            $this->following()->attach($user->id);
            return true;
        }
        return false;
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $user)
    {
        if ($this->isFollowing($user)) {
            $this->following()->detach($user->id);
            return true;
        }
        return false;
    }

    /**
     * Update last seen timestamp.
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * Get searchable data for Scout.
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'location' => $this->location,
            'is_verified' => $this->is_verified,
            'followers_count' => $this->followers_count,
            'products_count' => $this->products_count,
            'average_rating' => $this->average_rating,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    /**
     * Get the indexable data array for the model.
     */
    public function shouldBeSearchable()
    {
        return $this->email_verified_at !== null;
    }
}

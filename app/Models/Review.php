<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewed_id',
        'rating',
        'title',
        'content',
        'is_public',
        'seller_response',
        'responded_at',
        'helpful_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_public' => 'boolean',
            'responded_at' => 'datetime',
            'helpful_count' => 'integer',
        ];
    }

    // Relations

    /**
     * Get the order this review is for.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the reviewer (who wrote the review).
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the reviewed user (who received the review).
     */
    public function reviewed()
    {
        return $this->belongsTo(User::class, 'reviewed_id');
    }

    // Scopes

    /**
     * Scope public reviews.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope reviews by rating.
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope positive reviews (4-5 stars).
     */
    public function scopePositive($query)
    {
        return $query->whereIn('rating', [4, 5]);
    }

    /**
     * Scope negative reviews (1-2 stars).
     */
    public function scopeNegative($query)
    {
        return $query->whereIn('rating', [1, 2]);
    }

    /**
     * Scope reviews with seller response.
     */
    public function scopeWithResponse($query)
    {
        return $query->whereNotNull('seller_response');
    }

    // Accessors

    /**
     * Get rating as stars.
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Check if review is positive.
     */
    public function getIsPositiveAttribute()
    {
        return $this->rating >= 4;
    }

    /**
     * Check if review is negative.
     */
    public function getIsNegativeAttribute()
    {
        return $this->rating <= 2;
    }

    /**
     * Check if seller has responded.
     */
    public function getHasResponseAttribute()
    {
        return !empty($this->seller_response);
    }

    /**
     * Get time since review.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods

    /**
     * Add seller response.
     */
    public function addResponse($response)
    {
        $this->update([
            'seller_response' => $response,
            'responded_at' => now(),
        ]);
    }

    /**
     * Mark review as helpful.
     */
    public function markAsHelpful()
    {
        $this->increment('helpful_count');
    }

    /**
     * Toggle public visibility.
     */
    public function toggleVisibility()
    {
        $this->update(['is_public' => !$this->is_public]);
    }
}
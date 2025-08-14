<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_type', // 'following', 'recommended', 'trending'
        'content_type', // 'product', 'live', 'story'
        'content_id',
        'priority',
        'is_active',
        'viewed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'viewed_at' => 'datetime',
        'priority' => 'integer',
    ];

    /**
     * Get the user that owns the feed item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product content if this is a product feed item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'content_id')->where('content_type', 'product');
    }

    /**
     * Get the live content if this is a live feed item.
     */
    public function live()
    {
        return $this->belongsTo(Live::class, 'content_id')->where('content_type', 'live');
    }

    /**
     * Get the story content if this is a story feed item.
     */
    public function story()
    {
        return $this->belongsTo(Story::class, 'content_id')->where('content_type', 'story');
    }

    /**
     * Scope to get active feed items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get feed items by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('feed_type', $type);
    }

    /**
     * Scope to get unviewed feed items.
     */
    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }

    /**
     * Mark feed item as viewed.
     */
    public function markAsViewed()
    {
        $this->update(['viewed_at' => now()]);
    }
}

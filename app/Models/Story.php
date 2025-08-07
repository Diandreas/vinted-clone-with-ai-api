<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'content',
        'media_url',
        'media_type',
        'thumbnail',
        'duration',
        'product_id',
        'text_overlay',
        'background_color',
        'views_count',
        'expires_at',
        'is_highlighted',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_highlighted' => 'boolean',
            'views_count' => 'integer',
            'duration' => 'integer',
            'settings' => 'array',
        ];
    }

    // Type constants
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_TEXT = 'text';
    const TYPE_PRODUCT = 'product';

    // Relations

    /**
     * Get the user that owns the story.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product featured in the story.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the story views.
     */
    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    /**
     * Get the users who viewed this story.
     */
    public function viewedByUsers()
    {
        return $this->belongsToMany(User::class, 'story_views')->withTimestamps();
    }

    // Scopes

    /**
     * Scope active stories (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope expired stories.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope highlighted stories.
     */
    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }

    /**
     * Scope stories by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors

    /**
     * Get the story media URL.
     */
    public function getMediaUrlFullAttribute()
    {
        return $this->media_url 
            ? asset('storage/stories/' . $this->media_url)
            : null;
    }

    /**
     * Get the story thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail 
            ? asset('storage/stories/thumbnails/' . $this->thumbnail)
            : $this->media_url_full;
    }

    /**
     * Check if story is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if story is active.
     */
    public function getIsActiveAttribute()
    {
        return !$this->is_expired;
    }

    /**
     * Get time remaining until expiry.
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->is_expired) {
            return null;
        }
        
        return $this->expires_at->diffForHumans();
    }

    // Methods

    /**
     * Check if user has viewed this story.
     */
    public function isViewedBy(User $user)
    {
        return $this->views()->where('user_id', $user->id)->exists();
    }

    /**
     * Record a view of this story.
     */
    public function recordView(User $user)
    {
        // Don't record views from the story owner
        if ($user->id === $this->user_id) {
            return;
        }

        // Check if user already viewed this story
        if ($this->isViewedBy($user)) {
            return;
        }

        $this->views()->create([
            'user_id' => $user->id,
            'viewed_at' => now(),
        ]);

        $this->increment('views_count');
    }

    /**
     * Set story as highlighted.
     */
    public function highlight()
    {
        $this->update(['is_highlighted' => true]);
    }

    /**
     * Remove highlighted status.
     */
    public function unhighlight()
    {
        $this->update(['is_highlighted' => false]);
    }

    /**
     * Extend story expiry.
     */
    public function extend($hours = 24)
    {
        $this->update([
            'expires_at' => $this->expires_at->addHours($hours)
        ]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($story) {
            // Set default expiry to 24 hours if not set
            if (empty($story->expires_at)) {
                $story->expires_at = now()->addHours(24);
            }
        });
    }
}
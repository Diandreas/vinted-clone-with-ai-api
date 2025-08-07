<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Live extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'stream_key',
        'stream_url',
        'thumbnail',
        'scheduled_at',
        'started_at',
        'ended_at',
        'viewers_count',
        'max_viewers',
        'likes_count',
        'comments_count',
        'duration_seconds',
        'is_featured',
        'tags',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_featured' => 'boolean',
            'tags' => 'array',
            'settings' => 'array',
            'viewers_count' => 'integer',
            'max_viewers' => 'integer',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_LIVE = 'live';
    const STATUS_ENDED = 'ended';
    const STATUS_CANCELLED = 'cancelled';

    // Relations

    /**
     * Get the user that owns the live.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the live's comments.
     */
    public function comments()
    {
        return $this->hasMany(LiveComment::class)->latest();
    }

    /**
     * Get the live's likes.
     */
    public function likes()
    {
        return $this->hasMany(LiveLike::class);
    }

    /**
     * Get the users who liked this live.
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'live_likes');
    }

    /**
     * Get the reports for this live.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Scopes

    /**
     * Scope live streams.
     */
    public function scopeLive($query)
    {
        return $query->where('status', self::STATUS_LIVE);
    }

    /**
     * Scope scheduled streams.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope ended streams.
     */
    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    /**
     * Scope featured streams.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope streams starting soon.
     */
    public function scopeStartingSoon($query, $hours = 2)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->whereBetween('scheduled_at', [now(), now()->addHours($hours)]);
    }

    /**
     * Scope trending streams.
     */
    public function scopeTrending($query)
    {
        return $query->where('status', self::STATUS_LIVE)
                    ->orderBy('viewers_count', 'desc')
                    ->orderBy('likes_count', 'desc');
    }

    /**
     * Scope popular streams.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('max_viewers', 'desc')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('comments_count', 'desc');
    }

    // Accessors & Mutators

    /**
     * Get the live's thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail 
            ? asset('storage/live-thumbnails/' . $this->thumbnail)
            : asset('images/live-placeholder.jpg');
    }

    /**
     * Get the live's full stream URL.
     */
    public function getFullStreamUrlAttribute()
    {
        if (!$this->stream_url) {
            return null;
        }
        
        return $this->stream_url . '?key=' . $this->stream_key;
    }

    /**
     * Check if the live is currently active.
     */
    public function getIsLiveAttribute()
    {
        return $this->status === self::STATUS_LIVE;
    }

    /**
     * Check if the live is scheduled.
     */
    public function getIsScheduledAttribute()
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /**
     * Check if the live has ended.
     */
    public function getIsEndedAttribute()
    {
        return in_array($this->status, [self::STATUS_ENDED, self::STATUS_CANCELLED]);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return '0:00';
        }
        
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%d:%02d', $minutes, $seconds);
        }
    }

    /**
     * Get time until live starts (for scheduled lives).
     */
    public function getTimeUntilStartAttribute()
    {
        if ($this->status !== self::STATUS_SCHEDULED || !$this->scheduled_at) {
            return null;
        }
        
        return $this->scheduled_at->diffForHumans();
    }

    /**
     * Get time since live started (for live streams).
     */
    public function getTimeSinceStartAttribute()
    {
        if ($this->status !== self::STATUS_LIVE || !$this->started_at) {
            return null;
        }
        
        return $this->started_at->diffForHumans();
    }

    /**
     * Get engagement rate.
     */
    public function getEngagementRateAttribute()
    {
        if ($this->max_viewers == 0) {
            return 0;
        }
        
        $interactions = $this->likes_count + $this->comments_count;
        return round(($interactions / $this->max_viewers) * 100, 2);
    }

    // Methods

    /**
     * Check if user has liked this live.
     */
    public function isLikedBy(User $user)
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Like/unlike the live by a user.
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
     * Start the live stream.
     */
    public function start()
    {
        if ($this->status !== self::STATUS_SCHEDULED && $this->status !== self::STATUS_LIVE) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_LIVE,
            'started_at' => now(),
        ]);

        // Update user's live status
        $this->user->update(['is_live' => true]);

        // Fire live started event
        event(new \App\Events\LiveStarted($this));

        return true;
    }

    /**
     * End the live stream.
     */
    public function end()
    {
        if ($this->status !== self::STATUS_LIVE) {
            return false;
        }

        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
            'duration_seconds' => $duration,
        ]);

        // Update user's live status
        $this->user->update(['is_live' => false]);

        // Fire live ended event
        event(new \App\Events\LiveEnded($this));

        return true;
    }

    /**
     * Cancel the live stream.
     */
    public function cancel()
    {
        if (!in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_LIVE])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'ended_at' => now(),
        ]);

        // Update user's live status if was live
        if ($this->status === self::STATUS_LIVE) {
            $this->user->update(['is_live' => false]);
        }

        return true;
    }

    /**
     * Join a viewer to the live.
     */
    public function addViewer()
    {
        if ($this->status === self::STATUS_LIVE) {
            $this->increment('viewers_count');
            
            // Update max viewers if necessary
            if ($this->viewers_count > $this->max_viewers) {
                $this->update(['max_viewers' => $this->viewers_count]);
            }
        }
    }

    /**
     * Remove a viewer from the live.
     */
    public function removeViewer()
    {
        if ($this->status === self::STATUS_LIVE && $this->viewers_count > 0) {
            $this->decrement('viewers_count');
        }
    }

    /**
     * Add a comment to the live.
     */
    public function addComment(User $user, string $content)
    {
        if ($this->status !== self::STATUS_LIVE) {
            return null;
        }

        $comment = $this->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
        ]);

        $this->increment('comments_count');

        // Fire message sent event for real-time updates
        event(new \App\Events\MessageSent($comment));

        return $comment;
    }

    /**
     * Check if live can be started.
     */
    public function canStart()
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_LIVE]);
    }

    /**
     * Check if live can be ended.
     */
    public function canEnd()
    {
        return $this->status === self::STATUS_LIVE;
    }

    /**
     * Check if live can be cancelled.
     */
    public function canCancel()
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_LIVE]);
    }

    /**
     * Get live statistics.
     */
    public function getStats()
    {
        return [
            'total_viewers' => $this->max_viewers,
            'current_viewers' => $this->viewers_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'duration' => $this->formatted_duration,
            'engagement_rate' => $this->engagement_rate,
            'status' => $this->status,
        ];
    }

    /**
     * Generate a new stream key.
     */
    public function regenerateStreamKey()
    {
        $this->update([
            'stream_key' => \Illuminate\Support\Str::random(32)
        ]);
        
        return $this->stream_key;
    }

    /**
     * Set live as featured.
     */
    public function feature()
    {
        $this->update(['is_featured' => true]);
    }

    /**
     * Remove featured status.
     */
    public function unfeature()
    {
        $this->update(['is_featured' => false]);
    }
}

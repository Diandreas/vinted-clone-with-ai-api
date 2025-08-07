<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'follower_id',
        'following_id',
        'notification_enabled',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'notification_enabled' => 'boolean',
        ];
    }

    // Relations

    /**
     * Get the follower user.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Get the following user.
     */
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    // Methods

    /**
     * Toggle notifications for this follow.
     */
    public function toggleNotifications()
    {
        $this->update(['notification_enabled' => !$this->notification_enabled]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($follow) {
            // Fire user followed event
            event(new \App\Events\UserFollowed($follow));
        });
    }
}
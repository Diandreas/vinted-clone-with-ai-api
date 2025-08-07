<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'attachment_url',
        'attachment_type',
        'read_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Type constants
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_OFFER = 'offer';
    const TYPE_SYSTEM = 'system';

    // Relations

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the reports for this message.
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Scopes

    /**
     * Scope unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope read messages.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope messages by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors

    /**
     * Get the attachment URL.
     */
    public function getAttachmentUrlFullAttribute()
    {
        return $this->attachment_url 
            ? asset('storage/messages/' . $this->attachment_url)
            : null;
    }

    /**
     * Check if message is read.
     */
    public function getIsReadAttribute()
    {
        return $this->read_at !== null;
    }

    /**
     * Check if message is unread.
     */
    public function getIsUnreadAttribute()
    {
        return $this->read_at === null;
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Get time ago.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message can be edited by user.
     */
    public function canEdit(User $user)
    {
        return $this->sender_id === $user->id 
               && $this->created_at->isAfter(now()->subMinutes(5))
               && $this->type === self::TYPE_TEXT;
    }

    /**
     * Check if message can be deleted by user.
     */
    public function canDelete(User $user)
    {
        return $this->sender_id === $user->id 
               && $this->created_at->isAfter(now()->subHour());
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($message) {
            // Update conversation's last message time
            $message->conversation->updateLastMessageTime();
            
            // Fire message sent event
            event(new \App\Events\MessageSent($message));
        });
    }
}
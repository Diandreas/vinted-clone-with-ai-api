<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
        'is_archived',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_archived' => 'boolean',
        ];
    }

    // Relations

    /**
     * Get the product this conversation is about.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the buyer user.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller user.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the participants of this conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user');
    }

    /**
     * Get the messages in this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Get the last message.
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // Scopes

    /**
     * Scope conversations for a user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)
              ->orWhere('seller_id', $user->id);
        });
    }

    /**
     * Scope active conversations (not archived).
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope archived conversations.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    // Accessors

    /**
     * Get the other participant for a given user.
     */
    public function getOtherParticipant(User $user)
    {
        if ($this->buyer_id === $user->id) {
            return $this->seller;
        } elseif ($this->seller_id === $user->id) {
            return $this->buyer;
        }
        
        return null;
    }

    /**
     * Get unread messages count for a user.
     */
    public function getUnreadCount(User $user)
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Check if conversation has unread messages for user.
     */
    public function hasUnreadMessages(User $user)
    {
        return $this->getUnreadCount($user) > 0;
    }

    // Methods

    /**
     * Add a participant to the conversation.
     */
    public function addParticipant(User $user)
    {
        if (!$this->participants->contains($user->id)) {
            $this->participants()->attach($user->id);
        }
    }

    /**
     * Remove a participant from the conversation.
     */
    public function removeParticipant(User $user)
    {
        $this->participants()->detach($user->id);
    }

    /**
     * Mark all messages as read for a user.
     */
    public function markAsRead(User $user)
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Archive the conversation.
     */
    public function archive()
    {
        $this->update(['is_archived' => true]);
    }

    /**
     * Unarchive the conversation.
     */
    public function unarchive()
    {
        $this->update(['is_archived' => false]);
    }

    /**
     * Update last message timestamp.
     */
    public function updateLastMessageTime()
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Create or get conversation for users and product.
     */
    public static function findOrCreateForProduct(User $buyer, User $seller, Product $product)
    {
        return static::firstOrCreate([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
    }
}
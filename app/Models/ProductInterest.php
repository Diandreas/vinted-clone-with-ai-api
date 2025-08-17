<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductInterest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'status',
        'last_offered_price',
        'notes',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'last_offered_price' => 'decimal:2',
            'last_interaction_at' => 'datetime',
        ];
    }

    // Status constants
    const STATUS_INTERESTED = 'interested';
    const STATUS_NEGOTIATING = 'negotiating';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PURCHASED = 'purchased';

    // Relations

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'product_id', 'product_id')
                    ->where('buyer_id', $this->user_id);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_INTERESTED, self::STATUS_NEGOTIATING]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Methods

    public function updateInteraction()
    {
        $this->update(['last_interaction_at' => now()]);
    }

    public function markAsNegotiating($offeredPrice = null)
    {
        $data = ['status' => self::STATUS_NEGOTIATING];
        if ($offeredPrice) {
            $data['last_offered_price'] = $offeredPrice;
        }
        $this->update($data);
        $this->updateInteraction();
    }

    public function markAsPurchased()
    {
        $this->update(['status' => self::STATUS_PURCHASED]);
        $this->updateInteraction();
    }

    public function markAsCancelled()
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
        $this->updateInteraction();
    }

    public static function createOrUpdate($productId, $userId, $data = [])
    {
        return static::updateOrCreate(
            ['product_id' => $productId, 'user_id' => $userId],
            array_merge(['last_interaction_at' => now()], $data)
        );
    }

    // Accessors

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_INTERESTED => 'Intéressé',
            self::STATUS_NEGOTIATING => 'En négociation',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_PURCHASED => 'Acheté',
            default => 'Inconnu'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_INTERESTED => 'blue',
            self::STATUS_NEGOTIATING => 'orange',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_PURCHASED => 'green',
            default => 'gray'
        };
    }
}
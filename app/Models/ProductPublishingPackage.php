<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductPublishingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'product_count',
        'estimated_total_value',
        'total_fee',
        'fee_breakdown',
        'status',
        'payment_gateway_id',
        'payment_link',
        'expires_at',
        'paid_at',
        'product_slots',
        'used_slots',
        'payment_details',
    ];

    protected $casts = [
        'estimated_total_value' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'fee_breakdown' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'product_slots' => 'array',
        'payment_details' => 'array',
        'used_slots' => 'integer',
        'product_count' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Relations

    /**
     * Get the user that owns the package.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get products published using this package.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'publishing_package_id');
    }

    // Scopes

    /**
     * Scope active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PAID)
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope pending packages.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope expired packages.
     */
    public function scopeExpired($query)
    {
        return $query->where(function($query) {
            $query->where('status', self::STATUS_EXPIRED)
                  ->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * Scope paid packages.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    // Accessors & Mutators

    /**
     * Get formatted total fee.
     */
    public function getFormattedTotalFeeAttribute()
    {
        return number_format($this->total_fee, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Get formatted estimated total value.
     */
    public function getFormattedEstimatedValueAttribute()
    {
        if ($this->estimated_total_value === null) {
            return null;
        }
        return number_format($this->estimated_total_value, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Check if package is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast() || $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if package is paid and active.
     */
    public function getIsActiveAttribute()
    {
        return $this->status === self::STATUS_PAID && $this->expires_at->isFuture();
    }

    /**
     * Check if package can be used (has available slots).
     */
    public function getCanBeUsedAttribute()
    {
        return $this->is_active && $this->used_slots < $this->product_count;
    }

    /**
     * Get available slots remaining.
     */
    public function getAvailableSlotsAttribute()
    {
        return max(0, $this->product_count - $this->used_slots);
    }

    /**
     * Get usage percentage.
     */
    public function getUsagePercentageAttribute()
    {
        if ($this->product_count === 0) {
            return 0;
        }
        return round(($this->used_slots / $this->product_count) * 100, 1);
    }

    /**
     * Get time until expiration.
     */
    public function getTimeUntilExpirationAttribute()
    {
        if ($this->is_expired) {
            return null;
        }
        return $this->expires_at->diffForHumans();
    }

    // Methods

    /**
     * Mark package as paid.
     */
    public function markAsPaid(array $paymentDetails = [])
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_details' => $paymentDetails,
        ]);
    }

    /**
     * Mark package as expired.
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Cancel the package.
     */
    public function cancel()
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Use a slot from the package.
     */
    public function useSlot()
    {
        if (!$this->can_be_used) {
            throw new \Exception('Package cannot be used: ' . 
                ($this->is_expired ? 'expired' : 'no available slots'));
        }

        $this->increment('used_slots');
        
        return $this->fresh();
    }

    /**
     * Check if package can accommodate products.
     */
    public function canAccommodate(int $productCount = 1)
    {
        return $this->can_be_used && $this->available_slots >= $productCount;
    }

    /**
     * Reserve slots for products.
     */
    public function reserveSlots(array $productData)
    {
        if (!$this->canAccommodate(count($productData))) {
            throw new \Exception('Not enough slots available');
        }

        $currentSlots = $this->product_slots ?? [];
        
        foreach ($productData as $data) {
            $currentSlots[] = [
                'reserved_at' => now()->toISOString(),
                'product_data' => $data,
                'used' => false,
            ];
        }

        $this->update([
            'product_slots' => $currentSlots,
        ]);
    }

    /**
     * Use a reserved slot for a product.
     */
    public function useReservedSlot(int $slotIndex, int $productId)
    {
        $slots = $this->product_slots ?? [];
        
        if (!isset($slots[$slotIndex]) || $slots[$slotIndex]['used']) {
            throw new \Exception('Invalid or already used slot');
        }

        $slots[$slotIndex]['used'] = true;
        $slots[$slotIndex]['product_id'] = $productId;
        $slots[$slotIndex]['used_at'] = now()->toISOString();

        $this->update([
            'product_slots' => $slots,
            'used_slots' => $this->used_slots + 1,
        ]);
    }

    /**
     * Get package summary for display.
     */
    public function getSummary()
    {
        return [
            'package_id' => $this->package_id,
            'product_count' => $this->product_count,
            'total_fee' => $this->total_fee,
            'formatted_fee' => $this->formatted_total_fee,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_expired' => $this->is_expired,
            'can_be_used' => $this->can_be_used,
            'used_slots' => $this->used_slots,
            'available_slots' => $this->available_slots,
            'usage_percentage' => $this->usage_percentage,
            'expires_at' => $this->expires_at->toISOString(),
            'time_until_expiration' => $this->time_until_expiration,
            'paid_at' => $this->paid_at?->toISOString(),
        ];
    }

    /**
     * Generate a unique package ID.
     */
    public static function generatePackageId(int $userId)
    {
        return 'PKG-' . $userId . '-' . time() . '-' . substr(md5(uniqid()), 0, 8);
    }
}
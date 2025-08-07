<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'order_number',
        'status',
        'payment_status',
        'shipping_status',
        'total_amount',
        'product_price',
        'shipping_cost',
        'service_fee',
        'seller_earnings',
        'payment_method',
        'payment_intent_id',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
        'dispute_opened_at',
        'dispute_resolved_at',
        'shipping_address',
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'product_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'seller_earnings' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'dispute_opened_at' => 'datetime',
            'dispute_resolved_at' => 'datetime',
            'shipping_address' => 'array',
            'metadata' => 'array',
        ];
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_DISPUTED = 'disputed';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    const SHIPPING_STATUS_PENDING = 'pending';
    const SHIPPING_STATUS_SHIPPED = 'shipped';
    const SHIPPING_STATUS_DELIVERED = 'delivered';

    // Relations

    /**
     * Get the buyer (user who made the order).
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller (user who owns the product).
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the product being ordered.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the review for this order.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes

    /**
     * Scope orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_DELIVERED, self::STATUS_REFUNDED]);
    }

    /**
     * Scope active orders (not cancelled or refunded).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_REFUNDED]);
    }

    // Accessors

    /**
     * Check if order is pending.
     */
    public function getIsPendingAttribute()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if order is completed.
     */
    public function getIsCompletedAttribute()
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_REFUNDED]);
    }

    /**
     * Check if order can be cancelled.
     */
    public function getCanCancelAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if order can be shipped.
     */
    public function getCanShipAttribute()
    {
        return $this->status === self::STATUS_CONFIRMED && $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if order can be reviewed.
     */
    public function getCanReviewAttribute()
    {
        return $this->status === self::STATUS_DELIVERED && !$this->review;
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute()
    {
        return '€' . number_format($this->total_amount, 2);
    }

    // Methods

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (self::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals()
    {
        $productPrice = $this->product_price ?? $this->product->price;
        $shippingCost = $this->shipping_cost ?? 0;
        $serviceFee = round($productPrice * 0.05, 2); // 5% service fee
        $totalAmount = $productPrice + $shippingCost + $serviceFee;
        $sellerEarnings = $productPrice - $serviceFee;

        $this->update([
            'product_price' => $productPrice,
            'shipping_cost' => $shippingCost,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
            'seller_earnings' => $sellerEarnings,
        ]);
    }

    /**
     * Confirm the order.
     */
    public function confirm()
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
        ]);

        event(new \App\Events\OrderStatusChanged($this, self::STATUS_CONFIRMED));
    }

    /**
     * Ship the order.
     */
    public function ship($trackingNumber = null)
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'shipping_status' => self::SHIPPING_STATUS_SHIPPED,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ]);

        event(new \App\Events\OrderStatusChanged($this, self::STATUS_SHIPPED));
    }

    /**
     * Mark order as delivered.
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'shipping_status' => self::SHIPPING_STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        // Mark product as sold
        $this->product->markAsSold();

        event(new \App\Events\OrderStatusChanged($this, self::STATUS_DELIVERED));
    }

    /**
     * Cancel the order.
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => $reason,
        ]);

        event(new \App\Events\OrderStatusChanged($this, self::STATUS_CANCELLED));
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }
}

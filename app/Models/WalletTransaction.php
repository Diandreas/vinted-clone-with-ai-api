<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purpose', // 'topup', 'payout', 'order_payment', 'refund'
        'amount_xaf',
        'status', // 'pending', 'completed', 'failed', 'cancelled'
        'provider', // 'fapshi', 'om', 'momo', 'internal'
        'trans_id', // External transaction ID
        'order_id', // Related order if applicable
        'metadata', // Additional data
    ];

    protected $casts = [
        'amount_xaf' => 'integer',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Purpose constants
    const PURPOSE_TOPUP = 'topup';
    const PURPOSE_PAYOUT = 'payout';
    const PURPOSE_ORDER_PAYMENT = 'order_payment';
    const PURPOSE_REFUND = 'refund';

    // Provider constants
    const PROVIDER_FAPSHI = 'fapshi';
    const PROVIDER_OM = 'om';
    const PROVIDER_MOMO = 'momo';
    const PROVIDER_INTERNAL = 'internal';

    // Relations

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the related product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes

    /**
     * Scope completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope topup transactions.
     */
    public function scopeTopup($query)
    {
        return $query->where('purpose', self::PURPOSE_TOPUP);
    }

    /**
     * Scope payout transactions.
     */
    public function scopePayout($query)
    {
        return $query->where('purpose', self::PURPOSE_PAYOUT);
    }

    /**
     * Scope by provider.
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    // Accessors

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount_xaf) . ' Fcfa';
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_FAILED:
                return 'danger';
            case self::STATUS_CANCELLED:
                return 'secondary';
            default:
                return 'info';
        }
    }

    /**
     * Get purpose label.
     */
    public function getPurposeLabelAttribute()
    {
        switch ($this->purpose) {
            case self::PURPOSE_TOPUP:
                return 'Recharge';
            case self::PURPOSE_PAYOUT:
                return 'Retrait';
            case self::PURPOSE_ORDER_PAYMENT:
                return 'Achat';
            case self::PURPOSE_REFUND:
                return 'Remboursement';
            default:
                return ucfirst($this->purpose);
        }
    }

    /**
     * Get provider label.
     */
    public function getProviderLabelAttribute()
    {
        switch ($this->provider) {
            case self::PROVIDER_FAPSHI:
                return 'Fapshi';
            case self::PROVIDER_OM:
                return 'Orange Money';
            case self::PROVIDER_MOMO:
                return 'MTN Mobile Money';
            case self::PROVIDER_INTERNAL:
                return 'Interne';
            default:
                return ucfirst($this->provider);
        }
    }

    // Methods

    /**
     * Mark transaction as completed.
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed()
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Mark transaction as cancelled.
     */
    public function markAsCancelled()
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is failed.
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }
}




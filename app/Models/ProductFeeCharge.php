<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFeeCharge extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'fee_id',
        'amount',
        'status',
        'paid_at',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fee()
    {
        return $this->belongsTo(PlatformFee::class, 'fee_id');
    }
}




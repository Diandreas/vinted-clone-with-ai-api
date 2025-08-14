<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFeeCharge extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'platform_fee_id',
        'amount',
        'currency',
        'status',
        'meta',
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
        return $this->belongsTo(PlatformFee::class, 'platform_fee_id');
    }
}



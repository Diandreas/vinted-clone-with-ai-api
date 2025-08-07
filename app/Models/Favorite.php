<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    // Relations

    /**
     * Get the user who favorited.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Boot

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($favorite) {
            // Increment product favorites count
            $favorite->product->increment('favorites_count');
        });
        
        static::deleted(function ($favorite) {
            // Decrement product favorites count
            $favorite->product->decrement('favorites_count');
        });
    }
}
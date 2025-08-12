<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLike extends Model
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
     * Get the user who liked.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the liked product.
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
        
        static::created(function ($like) {
            // Increment product likes count
            $like->product->increment('likes_count');
            
            // Fire product liked event
            event(new \App\Events\ProductLiked($like));
        });
        
        static::deleted(function ($like) {
            // Decrement product likes count
            $like->product->decrement('likes_count');
        });
    }
}
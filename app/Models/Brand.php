<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'is_active',
        'is_premium',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relations

    /**
     * Get the products for this brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the active products for this brand.
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('status', Product::STATUS_ACTIVE);
    }

    // Scopes

    /**
     * Scope active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope premium brands.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors

    /**
     * Get the brand logo URL.
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo 
            ? asset('storage/brands/' . $this->logo)
            : asset('images/brand-default-logo.png');
    }

    /**
     * Get the brand's products count.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get the brand's active products count.
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->activeProducts()->count();
    }

    // Methods

    /**
     * Generate a slug from the name.
     */
    public function generateSlug()
    {
        $baseSlug = \Illuminate\Support\Str::slug($this->name);
        $slug = $baseSlug;
        $counter = 1;
        
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        return $slug;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = $brand->generateSlug();
            }
        });
        
        static::updating(function ($brand) {
            if ($brand->isDirty('name') && empty($brand->slug)) {
                $brand->slug = $brand->generateSlug();
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relations

    /**
     * Get the products for this condition.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the active products for this condition.
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('status', Product::STATUS_ACTIVE);
    }

    // Scopes

    /**
     * Scope active conditions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Get the condition icon URL.
     */
    public function getIconUrlAttribute()
    {
        return $this->icon 
            ? asset('storage/conditions/' . $this->icon)
            : asset('images/condition-default-icon.svg');
    }

    /**
     * Get the condition's products count.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get the condition's active products count.
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->activeProducts()->count();
    }
}

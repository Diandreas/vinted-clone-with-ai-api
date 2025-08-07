<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'parent_id',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
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
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the active products in this category.
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('status', Product::STATUS_ACTIVE);
    }

    // Scopes

    /**
     * Scope active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope ordered by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors & Mutators

    /**
     * Get the category icon URL.
     */
    public function getIconUrlAttribute()
    {
        return $this->icon 
            ? asset('storage/categories/icons/' . $this->icon)
            : asset('images/category-default-icon.svg');
    }

    /**
     * Get the category image URL.
     */
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/categories/images/' . $this->image)
            : asset('images/category-default-image.jpg');
    }

    /**
     * Get the category's products count.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get the category's active products count.
     */
    public function getActiveProductsCountAttribute()
    {
        return $this->activeProducts()->count();
    }

    /**
     * Check if category has children.
     */
    public function getHasChildrenAttribute()
    {
        return $this->children()->exists();
    }

    /**
     * Get the category's full path (breadcrumb).
     */
    public function getFullPathAttribute()
    {
        $path = collect([$this->name]);
        $parent = $this->parent;
        
        while ($parent) {
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $path->implode(' > ');
    }

    // Methods

    /**
     * Check if this category is a descendant of another category.
     */
    public function isDescendantOf(Category $category)
    {
        $parent = $this->parent;
        
        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }

    /**
     * Check if this category is an ancestor of another category.
     */
    public function isAncestorOf(Category $category)
    {
        return $category->isDescendantOf($this);
    }

    /**
     * Get all products in this category and its descendants.
     */
    public function getAllProducts()
    {
        $categoryIds = $this->getAllDescendantIds();
        $categoryIds[] = $this->id;
        
        return Product::whereIn('category_id', $categoryIds);
    }

    /**
     * Get all descendant category IDs.
     */
    public function getAllDescendantIds()
    {
        $ids = [];
        $children = $this->children;
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        
        return $ids;
    }

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
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = $category->generateSlug();
            }
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = $category->generateSlug();
            }
        });
    }
}

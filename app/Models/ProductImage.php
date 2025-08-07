<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'filename',
        'original_name',
        'alt_text',
        'order',
        'size',
        'width',
        'height',
        'mime_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    // Relations

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes

    /**
     * Scope ordered images.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Accessors

    /**
     * Get the full image URL.
     */
    public function getUrlAttribute()
    {
        return asset('storage/products/' . $this->filename);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        $pathInfo = pathinfo($this->filename);
        $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        return asset('storage/products/thumbnails/' . $thumbnailFilename);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute()
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        } elseif ($this->size < 1048576) {
            return round($this->size / 1024, 2) . ' KB';
        } else {
            return round($this->size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Get image dimensions string.
     */
    public function getDimensionsAttribute()
    {
        return $this->width . 'x' . $this->height;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVisionData extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'labels',
        'objects',
        'colors',
        'text',
        'faces',
        'web_entities',
        'similar_images',
        'similarity_score',
        'feature_vector',
        'processed',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'objects' => 'array',
            'colors' => 'array',
            'text' => 'array',
            'faces' => 'array',
            'web_entities' => 'array',
            'similar_images' => 'array',
            'similarity_score' => 'decimal:6',
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

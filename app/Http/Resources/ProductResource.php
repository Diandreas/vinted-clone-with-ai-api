<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'condition_id' => $this->condition_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'size' => $this->size,
            'color' => $this->color,
            'material' => $this->material,
            'status' => $this->status,
            'followers_only' => (bool) $this->followers_only,
            'is_spot' => (bool) $this->is_spot,
            'spot_starts_at' => $this->spot_starts_at,
            'spot_ends_at' => $this->spot_ends_at,
            'is_featured' => (bool) $this->is_featured,
            'is_boosted' => (bool) $this->is_boosted,
            'boosted_until' => $this->boosted_until,
            'views_count' => (int) $this->views_count,
            'likes_count' => (int) $this->likes_count,
            'favorites_count' => (int) $this->favorites_count,
            'comments_count' => (int) $this->comments_count,
            'sold_at' => $this->sold_at,
            'tags' => $this->tags,
            'measurements' => $this->measurements,
            'shipping_cost' => $this->shipping_cost,
            'location' => $this->location,
            'is_negotiable' => (bool) $this->is_negotiable,
            'minimum_offer' => $this->minimum_offer,
            'main_image' => $this->mainImage ? url('api/v1/files/products/' . $this->mainImage->filename) : asset('placeholder-product.jpg'),
            'main_image_url' => $this->main_image_url,
            'image_url' => $this->image_url,
            'uploaded_image' => $this->uploaded_image,
            'formatted_price' => $this->formatted_price,
            'formatted_original_price' => $this->formatted_original_price,
            'created_at_formatted' => $this->time_ago,
            'user' => $this->whenLoaded('user'),
            'category' => $this->whenLoaded('category'),
            'brand' => $this->whenLoaded('brand'),
            'condition' => $this->whenLoaded('condition'),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'thumbnail_url' => $image->thumbnail_url,
                        'mime_type' => $image->mime_type,
                        'is_video' => (bool) ($image->is_video ?? false),
                        'order' => $image->order,
                    ];
                });
            }),
            'is_liked' => (bool) ($this->is_liked ?? false),
            'is_favorited' => (bool) ($this->is_favorited ?? false),
            'is_liked_by_user' => (bool) ($this->is_liked_by_user ?? false),
            'is_favorited_by_user' => (bool) ($this->is_favorited_by_user ?? false),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

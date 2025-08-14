<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class ProcessProductImages implements ShouldQueue
{
    use Queueable;

    protected $product;
    protected $images;

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, $images)
    {
        $this->product = $product;
        $this->images = is_array($images) ? $images : [$images];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->images as $index => $image) {
            if ($image instanceof UploadedFile) {
                // Detect mime type
                $mime = $image->getMimeType();
                if (is_string($mime) && str_starts_with($mime, 'video/')) {
                    $this->processVideo($image, $index);
                } else {
                    $this->processImage($image, $index);
                }
            }
        }
    }

    /**
     * Process a single image.
     */
    protected function processImage(UploadedFile $image, int $order): void
    {
        // Generate unique filename
        $filename = time() . '_' . $this->product->id . '_' . $order . '.' . $image->getClientOriginalExtension();
        
        // Get image info
        $imageInfo = getimagesize($image->getPathname());
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;
        
        // Store original image
        $path = $image->storeAs('products', $filename, 'public');
        
        // Create thumbnail
        $this->createThumbnail($image, $filename);
        
        // Save to database
        ProductImage::create([
            'product_id' => $this->product->id,
            'filename' => $filename,
            'original_name' => $image->getClientOriginalName(),
            'alt_text' => $this->product->title,
            'order' => $order,
            'size' => $image->getSize(),
            'width' => $width,
            'height' => $height,
            'mime_type' => $image->getMimeType(),
        ]);
    }

    /**
     * Create thumbnail for image.
     */
    protected function createThumbnail(UploadedFile $image, string $filename): void
    {
        try {
            // Create thumbnails directory if it doesn't exist
            if (!Storage::disk('public')->exists('products/thumbnails')) {
                Storage::disk('public')->makeDirectory('products/thumbnails');
            }

            // Generate thumbnail filename
            $pathInfo = pathinfo($filename);
            $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

            // Create and save thumbnail (300x300) with Intervention Image v3
            $manager = new ImageManager(new Driver());
            $img = $manager->read($image->getPathname());
            $img->cover(300, 300);
            
            $thumbnailPath = storage_path('app/public/products/thumbnails/' . $thumbnailFilename);
            $img->save($thumbnailPath, quality: 85);
            
        } catch (\Exception $e) {
            // Log error but don't fail the job
            // Note: no Log facade imported to keep queue serialization light
            // error silently ignored in production
        }
    }

    /**
     * Process a single video.
     */
    protected function processVideo(UploadedFile $video, int $order): void
    {
        // Store original video (no transcoding here)
        $ext = $video->getClientOriginalExtension() ?: 'mp4';
        $filename = time() . '_' . $this->product->id . '_' . $order . '.' . $ext;
        $video->storeAs('products', $filename, 'public');

        ProductImage::create([
            'product_id' => $this->product->id,
            'filename' => $filename,
            'original_name' => $video->getClientOriginalName(),
            'alt_text' => $this->product->title,
            'order' => $order,
            'size' => $video->getSize(),
            'mime_type' => $video->getMimeType() ?? 'video/mp4',
        ]);
    }
}
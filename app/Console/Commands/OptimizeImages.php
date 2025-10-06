<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\ProductImage;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-images
                            {--quality=85 : Quality of compressed images (1-100)}
                            {--max-width=1920 : Maximum width for images}
                            {--max-height=1920 : Maximum height for images}
                            {--force : Re-optimize already optimized images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize and compress existing product images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quality = (int) $this->option('quality');
        $maxWidth = (int) $this->option('max-width');
        $maxHeight = (int) $this->option('max-height');
        $force = $this->option('force');

        // Validate quality
        if ($quality < 1 || $quality > 100) {
            $this->error('Quality must be between 1 and 100');
            return 1;
        }

        $this->info("Starting image optimization...");
        $this->info("Quality: {$quality}%, Max dimensions: {$maxWidth}x{$maxHeight}");

        // Get all images from storage
        $files = Storage::disk('public')->files('products');
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->warn('No images found in storage/app/public/products');
            return 0;
        }

        $this->info("Found {$totalFiles} images to process");
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $manager = new ImageManager(new Driver());
        $optimizedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $totalSavedBytes = 0;

        foreach ($files as $file) {
            $bar->advance();

            // Skip thumbnails directory
            if (str_contains($file, 'thumbnails/')) {
                $skippedCount++;
                continue;
            }

            $fullPath = storage_path('app/public/' . $file);

            // Skip if not an image
            if (!@getimagesize($fullPath)) {
                $skippedCount++;
                continue;
            }

            try {
                // Get original file size
                $originalSize = filesize($fullPath);

                // Check if already optimized (skip if file size is already small and not forced)
                if (!$force && $originalSize < 200000) { // Less than 200KB
                    $skippedCount++;
                    continue;
                }

                // Load and optimize image
                $image = $manager->read($fullPath);

                // Resize if needed
                if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                    $image->scale(width: $maxWidth, height: $maxHeight);
                }

                // Save optimized image
                $image->save($fullPath, quality: $quality);

                // Calculate saved space
                $newSize = filesize($fullPath);
                $savedBytes = $originalSize - $newSize;
                $totalSavedBytes += $savedBytes;

                $optimizedCount++;

                // Update database if exists
                $filename = basename($file);
                $productImage = ProductImage::where('filename', $filename)->first();
                if ($productImage) {
                    $productImage->update([
                        'size' => $newSize,
                        'width' => $image->width(),
                        'height' => $image->height()
                    ]);
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("Error processing {$file}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Show results
        $this->info("Optimization complete!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total images found', $totalFiles],
                ['Optimized', $optimizedCount],
                ['Skipped', $skippedCount],
                ['Errors', $errorCount],
                ['Space saved', $this->formatBytes($totalSavedBytes)],
            ]
        );

        // Optimize thumbnails as well
        if ($this->confirm('Do you want to optimize thumbnails too?', true)) {
            $this->optimizeThumbnails($quality);
        }

        return 0;
    }

    /**
     * Optimize thumbnail images
     */
    protected function optimizeThumbnails(int $quality): void
    {
        $files = Storage::disk('public')->files('products/thumbnails');
        $totalFiles = count($files);

        if ($totalFiles === 0) {
            $this->warn('No thumbnails found');
            return;
        }

        $this->info("Optimizing {$totalFiles} thumbnails...");
        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        $manager = new ImageManager(new Driver());
        $optimizedCount = 0;

        foreach ($files as $file) {
            $bar->advance();
            $fullPath = storage_path('app/public/' . $file);

            try {
                $image = $manager->read($fullPath);
                $image->save($fullPath, quality: $quality);
                $optimizedCount++;
            } catch (\Exception $e) {
                // Silent error for thumbnails
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Optimized {$optimizedCount} thumbnails");
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 0) {
            return '+' . $this->formatBytes(abs($bytes));
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

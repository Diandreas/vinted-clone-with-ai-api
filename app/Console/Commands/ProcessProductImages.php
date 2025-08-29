<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVisionData;
use App\Services\Vision\GoogleVisionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vision:process-products 
                            {--limit=50 : Number of products to process}
                            {--force : Process products even if they already have vision data}
                            {--product-id= : Process a specific product by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process product images with Google Vision API for image search functionality';

    private $visionService;

    /**
     * Create a new command instance.
     */
    public function __construct(GoogleVisionService $visionService)
    {
        parent::__construct();
        $this->visionService = $visionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting product image processing...');

        // Check if Google Vision service is enabled
        if (!$this->visionService->isEnabled()) {
            $this->error('❌ Google Vision service is not properly configured');
            $this->warn('Please ensure the Google Cloud credentials file exists and is valid');
            return self::FAILURE;
        }

        $limit = $this->option('limit');
        $force = $this->option('force');
        $productId = $this->option('product-id');

        if ($productId) {
            return $this->processSingleProduct($productId, $force);
        }

        return $this->processMultipleProducts($limit, $force);
    }

    private function processSingleProduct($productId, $force)
    {
        $product = Product::with('images')->find($productId);
        
        if (!$product) {
            $this->error("Product with ID {$productId} not found.");
            return Command::FAILURE;
        }

        // Check if already processed
        if (!$force && $product->visionData()->where('processed', true)->exists()) {
            $this->warn("Product {$product->id} already has vision data. Use --force to reprocess.");
            return Command::SUCCESS;
        }

        $this->info("Processing product: {$product->title}");
        
        $result = $this->processProduct($product);
        
        if ($result) {
            $this->info("✅ Successfully processed product {$product->id}");
            return Command::SUCCESS;
        } else {
            $this->error("❌ Failed to process product {$product->id}");
            return Command::FAILURE;
        }
    }

    private function processMultipleProducts($limit, $force)
    {
        $query = Product::with(['images', 'visionData'])
            ->where('status', 'active');

        if (!$force) {
            $query->whereDoesntHave('visionData', function ($query) {
                $query->where('processed', true);
            });
        }

        $products = $query->limit($limit)->get();

        if ($products->count() === 0) {
            $this->info('No products to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$products->count()} products to process.");
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $processed = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                if ($this->processProduct($product)) {
                    $processed++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Error processing product {$product->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✅ Processing complete!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total products', $products->count()],
                ['Successfully processed', $processed],
                ['Errors', $errors],
                ['Success rate', $products->count() > 0 ? round(($processed / $products->count()) * 100, 1) . '%' : '0%']
            ]
        );

        return Command::SUCCESS;
    }

    private function processProduct(Product $product)
    {
        $mainImage = $product->images->first();
        
        if (!$mainImage) {
            $this->warn("No images found for product {$product->id}");
            return false;
        }

        $imagePath = storage_path('app/public/products/' . $mainImage->filename);
        
        if (!file_exists($imagePath)) {
            $this->warn("Image file not found: {$imagePath}");
            return false;
        }

        try {
            // Delete existing vision data if forcing reprocessing
            if ($this->option('force')) {
                $product->visionData()->delete();
            }

            $analysis = $this->visionService->analyzeImage($imagePath, $product->id);
            
            if ($analysis) {
                $this->line("  📊 Labels: " . count($analysis['vision_data']['labels'] ?? []));
                $this->line("  🎯 Objects: " . count($analysis['vision_data']['objects'] ?? []));
                $this->line("  🎨 Colors: " . count($analysis['vision_data']['colors'] ?? []));
                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->error("Error processing {$product->id}: " . $e->getMessage());
            Log::error("Vision processing error for product {$product->id}: " . $e->getMessage());
            return false;
        }
    }
}

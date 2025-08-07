<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;
use App\Models\Live;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_product_lifecycle_workflow()
    {
        // Setup: Create necessary data
        $seller = User::factory()->create(['name' => 'Alice Seller']);
        $buyer = User::factory()->create(['name' => 'Bob Buyer']);
        $category = Category::factory()->create(['name' => 'T-Shirts']);
        $brand = Brand::factory()->create(['name' => 'Nike']);
        $condition = Condition::factory()->create(['name' => 'Like New']);

        // Step 1: Seller creates a product
        Sanctum::actingAs($seller);
        
        $productData = [
            'title' => 'Nike Air Max T-Shirt',
            'description' => 'Beautiful Nike t-shirt in excellent condition',
            'price' => 29.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'condition_id' => $condition->id,
            'size' => 'M',
            'color' => 'Blue',
            'images' => [
                \Illuminate\Http\UploadedFile::fake()->image('tshirt1.jpg'),
            ]
        ];

        $createResponse = $this->postJson('/api/v1/products', $productData);
        $createResponse->assertStatus(201);
        $product = Product::latest()->first();

        // Step 2: Public user views product (unauthenticated)
        $this->withoutExceptionHandling();
        $viewResponse = $this->getJson("/api/v1/products/{$product->id}");
        $viewResponse->assertStatus(200);
        
        // Verify view was recorded
        $this->assertEquals(1, $product->fresh()->views_count);

        // Step 3: Buyer likes the product
        Sanctum::actingAs($buyer);
        
        $likeResponse = $this->postJson("/api/v1/products/{$product->id}/like");
        $likeResponse->assertStatus(200)
                    ->assertJson(['liked' => true, 'likes_count' => 1]);

        // Step 4: Buyer adds product to favorites
        $favoriteResponse = $this->postJson("/api/v1/products/{$product->id}/favorite");
        $favoriteResponse->assertStatus(200)
                         ->assertJson(['favorited' => true, 'favorites_count' => 1]);

        // Step 5: Buyer comments on product
        $commentResponse = $this->postJson("/api/v1/products/{$product->id}/comment", [
            'content' => 'Is this still available?'
        ]);
        $commentResponse->assertStatus(201);

        // Step 6: Check buyer's favorites list
        $favoritesResponse = $this->getJson('/api/v1/products/my-favorites');
        $favoritesResponse->assertStatus(200);
        $favorites = $favoritesResponse->json('data.data');
        $this->assertCount(1, $favorites);
        $this->assertEquals($product->id, $favorites[0]['id']);

        // Step 7: Seller boosts the product
        Sanctum::actingAs($seller);
        
        $boostResponse = $this->putJson("/api/v1/products/{$product->id}/boost", [
            'hours' => 24
        ]);
        $boostResponse->assertStatus(200);

        // Verify product is boosted
        $boostedProduct = $product->fresh();
        $this->assertTrue($boostedProduct->is_boosted);
        $this->assertNotNull($boostedProduct->boosted_until);

        // Step 8: Test product search and filtering
        $searchResponse = $this->getJson('/api/v1/products?search=Nike&category_id=' . $category->id);
        $searchResponse->assertStatus(200);
        $searchResults = $searchResponse->json('data.data');
        $this->assertGreaterThan(0, count($searchResults));

        // Step 9: Seller updates product
        $updateResponse = $this->putJson("/api/v1/products/{$product->id}", [
            'price' => 24.99,
            'description' => 'Updated description - price reduced!'
        ]);
        $updateResponse->assertStatus(200);

        // Verify changes
        $updatedProduct = $product->fresh();
        $this->assertEquals(24.99, $updatedProduct->price);
        $this->assertStringContains('price reduced', $updatedProduct->description);
    }

    /** @test */
    public function complete_live_streaming_workflow()
    {
        // Setup
        $streamer = User::factory()->create(['name' => 'Live Streamer']);
        $viewer1 = User::factory()->create(['name' => 'Viewer 1']);
        $viewer2 = User::factory()->create(['name' => 'Viewer 2']);

        // Step 1: Streamer creates a scheduled live
        Sanctum::actingAs($streamer);
        
        $liveData = [
            'title' => 'Fashion Haul Live Stream',
            'description' => 'Showing off new items in my closet',
            'scheduled_at' => now()->addHour()->toISOString()
        ];

        $createResponse = $this->postJson('/api/v1/lives', $liveData);
        $createResponse->assertStatus(201);
        $live = Live::latest()->first();

        $this->assertEquals(Live::STATUS_SCHEDULED, $live->status);
        $this->assertFalse($streamer->fresh()->is_live);

        // Step 2: Streamer starts the live
        $startResponse = $this->postJson("/api/v1/lives/{$live->id}/start");
        $startResponse->assertStatus(200);

        $startedLive = $live->fresh();
        $this->assertEquals(Live::STATUS_LIVE, $startedLive->status);
        $this->assertNotNull($startedLive->started_at);
        $this->assertTrue($streamer->fresh()->is_live);

        // Step 3: Viewers join the live
        $this->postJson("/api/v1/lives/{$live->id}/join");
        $this->postJson("/api/v1/lives/{$live->id}/join");

        $this->assertEquals(2, $live->fresh()->viewers_count);
        $this->assertEquals(2, $live->fresh()->max_viewers);

        // Step 4: Viewer 1 likes the live
        Sanctum::actingAs($viewer1);
        
        $likeResponse = $this->postJson("/api/v1/lives/{$live->id}/like");
        $likeResponse->assertStatus(200);
        $this->assertEquals(1, $live->fresh()->likes_count);

        // Step 5: Viewer 1 comments
        $commentResponse = $this->postJson("/api/v1/lives/{$live->id}/comment", [
            'content' => 'Great stream!'
        ]);
        $commentResponse->assertStatus(201);
        $this->assertEquals(1, $live->fresh()->comments_count);

        // Step 6: Viewer 2 also comments
        Sanctum::actingAs($viewer2);
        
        $this->postJson("/api/v1/lives/{$live->id}/comment", [
            'content' => 'Love the content!'
        ]);
        $this->assertEquals(2, $live->fresh()->comments_count);

        // Step 7: Get live comments
        $commentsResponse = $this->getJson("/api/v1/lives/{$live->id}/comments");
        $commentsResponse->assertStatus(200);
        $comments = $commentsResponse->json('data.data');
        $this->assertCount(2, $comments);

        // Step 8: One viewer leaves
        $this->postJson("/api/v1/lives/{$live->id}/leave");
        $this->assertEquals(1, $live->fresh()->viewers_count);
        $this->assertEquals(2, $live->fresh()->max_viewers); // Max should remain

        // Step 9: Streamer ends the live
        Sanctum::actingAs($streamer);
        
        $endResponse = $this->postJson("/api/v1/lives/{$live->id}/end");
        $endResponse->assertStatus(200);

        $endedLive = $live->fresh();
        $this->assertEquals(Live::STATUS_ENDED, $endedLive->status);
        $this->assertNotNull($endedLive->ended_at);
        $this->assertFalse($streamer->fresh()->is_live);
    }

    /** @test */
    public function user_follow_and_social_interaction_workflow()
    {
        // Setup
        $influencer = User::factory()->create(['name' => 'Fashion Influencer']);
        $follower = User::factory()->create(['name' => 'Fashion Fan']);

        // Step 1: User follows influencer
        Sanctum::actingAs($follower);
        
        $followResponse = $this->postJson("/api/v1/users/{$influencer->id}/follow");
        $followResponse->assertStatus(200);

        $this->assertTrue($follower->isFollowing($influencer));
        $this->assertEquals(1, $influencer->fresh()->followers_count);
        $this->assertEquals(1, $follower->fresh()->following_count);

        // Step 2: Check following lists
        $followingResponse = $this->getJson('/api/v1/users/my-following');
        $followingResponse->assertStatus(200);
        $following = $followingResponse->json('data');
        $this->assertCount(1, $following);

        // Step 3: Influencer creates multiple products
        Sanctum::actingAs($influencer);
        
        $category = Category::factory()->create();
        $condition = Condition::factory()->create();
        
        for ($i = 1; $i <= 3; $i++) {
            Product::factory()->create([
                'user_id' => $influencer->id,
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'title' => "Product {$i}",
                'status' => Product::STATUS_ACTIVE
            ]);
        }

        // Step 4: Check influencer's products
        $productsResponse = $this->getJson('/api/v1/products/my-products');
        $productsResponse->assertStatus(200);
        $products = $productsResponse->json('data.data');
        $this->assertCount(3, $products);

        // Step 5: Follower views influencer's profile products
        Sanctum::actingAs($follower);
        
        $profileResponse = $this->getJson("/api/v1/users/{$influencer->id}/products");
        $profileResponse->assertStatus(200);
        $profileProducts = $profileResponse->json('data.data');
        $this->assertCount(3, $profileProducts);

        // Step 6: Follower unfollows
        $unfollowResponse = $this->postJson("/api/v1/users/{$influencer->id}/unfollow");
        $unfollowResponse->assertStatus(200);

        $this->assertFalse($follower->fresh()->isFollowing($influencer));
        $this->assertEquals(0, $influencer->fresh()->followers_count);
    }

    /** @test */
    public function product_filtering_and_search_workflow()
    {
        // Setup: Create diverse products
        $category1 = Category::factory()->create(['name' => 'Dresses']);
        $category2 = Category::factory()->create(['name' => 'Shoes']);
        $brand1 = Brand::factory()->create(['name' => 'Zara']);
        $brand2 = Brand::factory()->create(['name' => 'Nike']);
        $condition = Condition::factory()->create();

        // Create products with different attributes
        Product::factory()->create([
            'title' => 'Beautiful Summer Dress',
            'price' => 45.00,
            'category_id' => $category1->id,
            'brand_id' => $brand1->id,
            'condition_id' => $condition->id,
            'size' => 'M',
            'color' => 'Blue',
            'status' => Product::STATUS_ACTIVE
        ]);

        Product::factory()->create([
            'title' => 'Nike Running Shoes',
            'price' => 89.99,
            'category_id' => $category2->id,
            'brand_id' => $brand2->id,
            'condition_id' => $condition->id,
            'size' => '42',
            'color' => 'Black',
            'status' => Product::STATUS_ACTIVE
        ]);

        Product::factory()->create([
            'title' => 'Vintage Blue Jeans',
            'price' => 25.00,
            'category_id' => $category1->id,
            'brand_id' => $brand1->id,
            'condition_id' => $condition->id,
            'size' => 'L',
            'color' => 'Blue',
            'status' => Product::STATUS_ACTIVE
        ]);

        // Test 1: Filter by category
        $categoryResponse = $this->getJson("/api/v1/products?category_id={$category1->id}");
        $categoryResponse->assertStatus(200);
        $categoryProducts = $categoryResponse->json('data.data');
        $this->assertCount(2, $categoryProducts);

        // Test 2: Filter by brand
        $brandResponse = $this->getJson("/api/v1/products?brand_id={$brand2->id}");
        $brandResponse->assertStatus(200);
        $brandProducts = $brandResponse->json('data.data');
        $this->assertCount(1, $brandProducts);

        // Test 3: Filter by price range
        $priceResponse = $this->getJson('/api/v1/products?min_price=30&max_price=50');
        $priceResponse->assertStatus(200);
        $priceProducts = $priceResponse->json('data.data');
        $this->assertCount(1, $priceProducts);

        // Test 4: Filter by color
        $colorResponse = $this->getJson('/api/v1/products?color=Blue');
        $colorResponse->assertStatus(200);
        $colorProducts = $colorResponse->json('data.data');
        $this->assertCount(2, $colorProducts);

        // Test 5: Text search
        $searchResponse = $this->getJson('/api/v1/products?search=Nike');
        $searchResponse->assertStatus(200);
        $searchProducts = $searchResponse->json('data.data');
        $this->assertCount(1, $searchProducts);

        // Test 6: Combined filters
        $combinedResponse = $this->getJson("/api/v1/products?category_id={$category1->id}&color=Blue&max_price=50");
        $combinedResponse->assertStatus(200);
        $combinedProducts = $combinedResponse->json('data.data');
        $this->assertCount(2, $combinedProducts);

        // Test 7: Sort by price (low to high)
        $sortResponse = $this->getJson('/api/v1/products?sort=price_low');
        $sortResponse->assertStatus(200);
        $sortedProducts = $sortResponse->json('data.data');
        
        // Verify sorting
        $this->assertEquals(25.00, $sortedProducts[0]['price']);
        $this->assertEquals(45.00, $sortedProducts[1]['price']);
        $this->assertEquals(89.99, $sortedProducts[2]['price']);
    }
}

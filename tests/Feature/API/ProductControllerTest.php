<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $condition;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->condition = Condition::factory()->create();
    }

    /** @test */
    public function it_can_list_products()
    {
        Product::factory()->count(5)->create(['status' => Product::STATUS_ACTIVE]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'price',
                                'status',
                                'user',
                                'category',
                                'condition'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_products_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        Product::factory()->create(['category_id' => $category1->id, 'status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['category_id' => $category2->id, 'status' => Product::STATUS_ACTIVE]);

        $response = $this->getJson("/api/v1/products?category_id={$category1->id}");

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertCount(1, $products);
        $this->assertEquals($category1->id, $products[0]['category']['id']);
    }

    /** @test */
    public function it_can_filter_products_by_price_range()
    {
        Product::factory()->create(['price' => 10.00, 'status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['price' => 25.00, 'status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['price' => 50.00, 'status' => Product::STATUS_ACTIVE]);

        $response = $this->getJson('/api/v1/products?min_price=15&max_price=30');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertCount(1, $products);
        $this->assertEquals(25.00, $products[0]['price']);
    }

    /** @test */
    public function it_can_search_products()
    {
        Product::factory()->create([
            'title' => 'Nike T-Shirt',
            'status' => Product::STATUS_ACTIVE
        ]);
        Product::factory()->create([
            'title' => 'Adidas Shoes',
            'status' => Product::STATUS_ACTIVE
        ]);

        $response = $this->getJson('/api/v1/products?search=Nike');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertCount(1, $products);
        $this->assertStringContainsString('Nike', $products[0]['title']);
    }

    /** @test */
    public function it_can_sort_products()
    {
        Product::factory()->create(['price' => 30.00, 'status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['price' => 10.00, 'status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['price' => 20.00, 'status' => Product::STATUS_ACTIVE]);

        $response = $this->getJson('/api/v1/products?sort=price_low');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertEquals(10.00, $products[0]['price']);
        $this->assertEquals(20.00, $products[1]['price']);
        $this->assertEquals(30.00, $products[2]['price']);
    }

    /** @test */
    public function authenticated_user_can_create_product()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->user);

        $productData = [
            'title' => 'Test Product',
            'description' => 'Test description',
            'price' => 29.99,
            'category_id' => $this->category->id,
            'condition_id' => $this->condition->id,
            'size' => 'M',
            'color' => 'Blue',
            'images' => [
                UploadedFile::fake()->image('product1.jpg'),
                UploadedFile::fake()->image('product2.jpg'),
            ]
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'price'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Test Product',
            'user_id' => $this->user->id,
            'status' => Product::STATUS_ACTIVE
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_product()
    {
        $productData = [
            'title' => 'Test Product',
            'description' => 'Test description',
            'price' => 29.99,
            'category_id' => $this->category->id,
            'condition_id' => $this->condition->id,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_product()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'title',
                    'description',
                    'price',
                    'category_id',
                    'condition_id',
                    'images'
                ]);
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'user',
                        'category',
                        'condition',
                        'images',
                        'comments'
                    ]
                ]);
    }

    /** @test */
    public function authenticated_user_can_like_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'liked' => true,
                    'likes_count' => 1
                ]);

        $this->assertTrue($product->isLikedBy($this->user));
    }

    /** @test */
    public function authenticated_user_can_unlike_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create();
        $product->toggleLike($this->user); // Like first

        $response = $this->postJson("/api/v1/products/{$product->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'liked' => false,
                    'likes_count' => 0
                ]);

        $this->assertFalse($product->isLikedBy($this->user));
    }

    /** @test */
    public function authenticated_user_can_favorite_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/favorite");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'favorited' => true,
                    'favorites_count' => 1
                ]);

        $this->assertTrue($product->isFavoritedBy($this->user));
    }

    /** @test */
    public function authenticated_user_can_add_comment_to_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/comment", [
            'content' => 'Great product!'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'content',
                        'user'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('product_comments', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'content' => 'Great product!'
        ]);
    }

    /** @test */
    public function owner_can_update_their_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'title' => 'Updated Title',
            'price' => 39.99
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Updated Title',
                        'price' => 39.99
                    ]
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
            'price' => 39.99
        ]);
    }

    /** @test */
    public function non_owner_cannot_update_product()
    {
        $owner = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);
        
        Sanctum::actingAs($this->user); // Different user

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function owner_can_delete_their_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_can_get_user_products()
    {
        Sanctum::actingAs($this->user);
        Product::factory()->count(3)->create(['user_id' => $this->user->id]);
        Product::factory()->count(2)->create(); // Other user's products

        $response = $this->getJson('/api/v1/products/my-products');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertCount(3, $products);
        foreach ($products as $product) {
            $this->assertEquals($this->user->id, $product['user']['id']);
        }
    }

    /** @test */
    public function it_can_get_user_favorites()
    {
        Sanctum::actingAs($this->user);
        $product1 = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
        $product2 = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
        Product::factory()->create(['status' => Product::STATUS_ACTIVE]); // Not favorited

        $product1->toggleFavorite($this->user);
        $product2->toggleFavorite($this->user);

        $response = $this->getJson('/api/v1/products/my-favorites');

        $response->assertStatus(200);
        $favorites = $response->json('data.data');
        
        $this->assertCount(2, $favorites);
    }

    /** @test */
    public function it_can_boost_product()
    {
        Sanctum::actingAs($this->user);
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/v1/products/{$product->id}/boost", [
            'hours' => 24
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product boosted successfully'
                ]);

        $boostedProduct = $product->fresh();
        $this->assertTrue($boostedProduct->is_boosted);
        $this->assertNotNull($boostedProduct->boosted_until);
    }

    /** @test */
    public function it_can_get_trending_products()
    {
        // Create products with different stats
        Product::factory()->create([
            'views_count' => 100,
            'likes_count' => 50,
            'created_at' => now()->subDays(3),
            'status' => Product::STATUS_ACTIVE
        ]);
        
        Product::factory()->create([
            'views_count' => 200,
            'likes_count' => 100,
            'created_at' => now()->subDays(10), // Too old
            'status' => Product::STATUS_ACTIVE
        ]);

        $response = $this->getJson('/api/v1/trending?days=7');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        $this->assertCount(1, $products);
    }

    /** @test */
    public function unauthenticated_user_cannot_like_product()
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/like");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_records_product_view()
    {
        $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
        $initialViews = $product->views_count;

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        
        // Should increment views count
        $this->assertEquals($initialViews + 1, $product->fresh()->views_count);
    }
}

<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_product()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $condition = Condition::factory()->create();

        $productData = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'title' => 'Test Product',
            'description' => 'Test description',
            'price' => 29.99,
            'status' => Product::STATUS_ACTIVE,
        ];

        $product = Product::create($productData);

        $this->assertDatabaseHas('products', [
            'title' => 'Test Product',
            'price' => 29.99,
            'status' => Product::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $product = Product::factory()->create([
            'price' => 29.99,
            'is_featured' => true,
            'tags' => ['vintage', 'designer'],
        ]);

        $this->assertTrue(is_numeric($product->price)); // Laravel peut retourner string ou float
        $this->assertIsBool($product->is_featured);
        $this->assertIsArray($product->tags);
    }

    /** @test */
    public function it_scopes_active_products()
    {
        $activeProduct = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);
        $draftProduct = Product::factory()->create(['status' => Product::STATUS_DRAFT]);

        $activeProducts = Product::active()->get();

        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($draftProduct));
    }

    /** @test */
    public function it_can_be_liked_and_unliked()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $liked = $product->toggleLike($user);
        $this->assertTrue($liked);
        $this->assertTrue($product->isLikedBy($user));

        $liked = $product->toggleLike($user);
        $this->assertFalse($liked);
        $this->assertFalse($product->isLikedBy($user));
    }

    /** @test */
    public function it_can_be_marked_as_sold()
    {
        $product = Product::factory()->create(['status' => Product::STATUS_ACTIVE]);

        $product->markAsSold();

        $this->assertEquals(Product::STATUS_SOLD, $product->fresh()->status);
        $this->assertNotNull($product->fresh()->sold_at);
    }
}
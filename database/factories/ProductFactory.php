<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Condition;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 200);
        $originalPrice = fake()->boolean(30) ? $price + fake()->randomFloat(2, 5, 50) : null;

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'brand_id' => fake()->boolean(80) ? Brand::factory() : null,
            'condition_id' => Condition::factory(),
            'title' => fake()->words(3, true) . ' ' . fake()->randomElement(['Shirt', 'Dress', 'Jeans', 'Jacket', 'Shoes', 'Bag']),
            'description' => fake()->paragraphs(2, true),
            'price' => $price,
            'original_price' => $originalPrice,
            'size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '38', '40', '42', '44']),
            'color' => fake()->randomElement(['Black', 'White', 'Blue', 'Red', 'Green', 'Yellow', 'Pink', 'Gray', 'Brown']),
            'material' => fake()->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Denim', 'Leather', 'Synthetic']),
            'status' => fake()->randomElement([
                Product::STATUS_ACTIVE,
                Product::STATUS_DRAFT,
                Product::STATUS_SOLD,
                Product::STATUS_RESERVED
            ]),
            'is_featured' => fake()->boolean(10), // 10% chance
            'is_boosted' => fake()->boolean(5), // 5% chance
            'boosted_until' => fake()->boolean(5) ? fake()->dateTimeBetween('now', '+1 week') : null,
            'views_count' => 0,
            'likes_count' => 0,
            'favorites_count' => 0,
            'comments_count' => 0,
            'sold_at' => null,
            'tags' => fake()->randomElements(['vintage', 'designer', 'casual', 'formal', 'trendy', 'rare'], fake()->numberBetween(0, 3)),
            'measurements' => fake()->boolean(30) ? [
                'chest' => fake()->numberBetween(80, 120) . 'cm',
                'length' => fake()->numberBetween(60, 80) . 'cm',
                'sleeve' => fake()->numberBetween(50, 70) . 'cm',
            ] : null,
            'shipping_cost' => fake()->randomFloat(2, 0, 15),
            'location' => fake()->city(),
            'is_negotiable' => fake()->boolean(40),
            'minimum_offer' => fake()->boolean(20) ? $price * 0.8 : null,
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the product is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_SOLD,
            'sold_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the product is boosted.
     */
    public function boosted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_boosted' => true,
            'boosted_until' => fake()->dateTimeBetween('now', '+1 week'),
        ]);
    }
}

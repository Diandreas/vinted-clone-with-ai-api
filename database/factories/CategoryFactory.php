<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'T-Shirts', 'Dresses', 'Jeans', 'Jackets', 'Shoes', 'Bags',
            'Accessories', 'Tops', 'Bottoms', 'Outerwear', 'Sportswear'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
            'icon' => null,
            'image' => null,
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'meta_title' => $name,
            'meta_description' => fake()->sentence(),
        ];
    }
}

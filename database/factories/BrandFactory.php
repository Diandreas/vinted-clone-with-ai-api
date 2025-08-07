<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Nike', 'Adidas', 'Zara', 'H&M', 'Uniqlo', 'Gucci', 'Prada',
            'Louis Vuitton', 'Chanel', 'Dior', 'Versace', 'Armani'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
            'logo' => null,
            'website' => fake()->url(),
            'is_active' => true,
            'is_premium' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}

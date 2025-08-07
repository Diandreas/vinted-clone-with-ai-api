<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reviewer_id' => User::factory(),
            'reviewed_id' => User::factory(),
            'order_id' => null, // Can be null for general reviews
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'is_public' => fake()->boolean(90), // 90% public
            'seller_response' => null,
            'responded_at' => null,
            'helpful_count' => fake()->numberBetween(0, 20),
        ];
    }

    /**
     * Create a review with seller response.
     */
    public function withResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'seller_response' => fake()->paragraph(),
            'responded_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a positive review (4-5 stars).
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(4, 5),
        ]);
    }

    /**
     * Create a negative review (1-2 stars).
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(1, 2),
        ]);
    }
}
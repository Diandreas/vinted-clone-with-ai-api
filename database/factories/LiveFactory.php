<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Live;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Live>
 */
class LiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement([Live::STATUS_SCHEDULED, Live::STATUS_LIVE, Live::STATUS_ENDED]),
            'stream_key' => Str::random(32),
            'stream_url' => fake()->url(),
            'thumbnail' => null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 week'),
            'started_at' => null,
            'ended_at' => null,
            'viewers_count' => fake()->numberBetween(0, 500),
            'max_viewers' => fake()->numberBetween(0, 1000),
            'likes_count' => fake()->numberBetween(0, 100),
            'comments_count' => fake()->numberBetween(0, 50),
            'duration_seconds' => 0,
            'is_featured' => fake()->boolean(10),
            'tags' => fake()->randomElements(['fashion', 'style', 'trendy', 'casual'], fake()->numberBetween(0, 2)),
            'settings' => [],
        ];
    }
}

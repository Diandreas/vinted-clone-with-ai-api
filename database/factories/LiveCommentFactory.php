<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Live;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LiveComment>
 */
class LiveCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'live_id' => Live::factory(),
            'user_id' => User::factory(),
            'content' => fake()->sentence(),
        ];
    }
}

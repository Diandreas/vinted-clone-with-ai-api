<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->sentence(10),
            'avatar' => null,
            'cover_image' => null,
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city(),
            'website' => fake()->url(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'is_verified' => fake()->boolean(20), // 20% chance of being verified
            'is_live' => false,
            'last_seen_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'settings' => [
                'theme' => fake()->randomElement(['light', 'dark']),
                'language' => 'en',
            ],
            'notification_settings' => [
                'email_notifications' => true,
                'push_notifications' => true,
                'marketing_emails' => fake()->boolean(),
            ],
            'privacy_settings' => [
                'profile_visibility' => 'public',
                'show_online_status' => true,
            ],
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

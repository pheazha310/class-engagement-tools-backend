<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'question' => fake()->sentence(),
            'poll_type' => fake()->randomElement(['multiple_choice', 'yes_no', 'rating']),
            'status' => 'draft',
            'duration_minutes' => null,
            'allow_multiple_votes' => false,
            'anonymous' => true,
            'show_results' => true,
            'created_by' => User::factory(),
            'started_at' => null,
            'ended_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'started_at' => now()->subMinutes(10),
            'ended_at' => now(),
        ]);
    }
}

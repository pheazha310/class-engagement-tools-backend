<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Poll>
 */
class PollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'question' => fake()->sentence(),
            'status' => 'draft',
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

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ended',
            'started_at' => now()->subMinutes(10),
            'ended_at' => now(),
        ]);
    }
}

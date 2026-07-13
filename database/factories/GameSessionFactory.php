<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSession>
 */
class GameSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'game_type' => fake()->randomElement(['poll', 'wheel']),
            'settings' => [
                'time_limit' => fake()->numberBetween(30, 300),
            ],
            'status' => 'active',
        ];
    }
}

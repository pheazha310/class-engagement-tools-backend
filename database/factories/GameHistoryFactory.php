<?php

namespace Database\Factories;

use App\Models\GameHistory;
use App\Models\GameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameHistory>
 */
class GameHistoryFactory extends Factory
{
    protected $model = GameHistory::class;

    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'teacher_id' => null,
            'game_type' => fake()->randomElement(['poll', 'wheel', 'math-challenge', 'vocabulary-race', 'quiz-battle', 'memory-game']),
            'settings' => [
                'time_limit' => fake()->numberBetween(30, 300),
            ],
            'participants' => [
                ['name' => fake()->name(), 'score' => fake()->numberBetween(0, 50)],
                ['name' => fake()->name(), 'score' => fake()->numberBetween(0, 50)],
            ],
            'scores' => [
                ['participant' => fake()->name(), 'score' => fake()->numberBetween(0, 50)],
                ['participant' => fake()->name(), 'score' => fake()->numberBetween(0, 50)],
            ],
            'total_questions' => fake()->numberBetween(5, 20),
            'started_at' => fake()->dateTimeBetween('-1 hour', '-30 minutes'),
            'ended_at' => fake()->dateTimeBetween('-30 minutes', 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\GameAnswer;
use App\Models\GameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameAnswer>
 */
class GameAnswerFactory extends Factory
{
    protected $model = GameAnswer::class;

    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'user_id' => null,
            'question_id' => (string) fake()->randomDigit(),
            'submitted_answer' => fake()->word(),
            'is_correct' => fake()->boolean(),
            'points_awarded' => fake()->numberBetween(0, 10),
            'participant_name' => fake()->name(),
        ];
    }
}

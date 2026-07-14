<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'subject' => fake()->randomElement(['Mathematics', 'English', 'Science', 'History', 'Khmer']),
            'class_name' => 'Grade '.fake()->numberBetween(7, 12).fake()->randomElement(['A', 'B', 'C']),
            'duration' => fake()->numberBetween(15, 120),
            'passing_score' => fake()->numberBetween(40, 80),
            'due_date' => fake()->dateTimeBetween('+1 day', '+1 month')->format('Y-m-d\TH:i'),
            'shuffle_questions' => fake()->boolean(),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}

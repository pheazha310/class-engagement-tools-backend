<?php

namespace Database\Factories;

use App\Models\TeacherActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherActivity>
 */
class TeacherActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => $this->faker->uuid,
            'activity' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'action' => $this->faker->word(),
            'status' => $this->faker->randomElement(['success', 'pending', 'failed']),
            'timestamp' => $this->faker->dateTimeThisYear(),
        ];
    }
}

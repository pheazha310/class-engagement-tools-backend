<?php

namespace Database\Factories;

use App\Models\ClassConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassConfiguration>
 */
class ClassConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'settings' => ['theme' => 'light', 'notifications' => true],
            'teacher_id' => $this->faker->uuid,
        ];
    }
}

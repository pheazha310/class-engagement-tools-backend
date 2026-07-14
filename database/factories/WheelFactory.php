<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wheel;
use App\Models\WheelTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wheel>
 */
class WheelFactory extends Factory
{
    protected $model = Wheel::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'color' => fake()->hexColor(),
            'theme_id' => WheelTheme::factory(),
            'removal_mode' => fake()->boolean(20),
        ];
    }
}

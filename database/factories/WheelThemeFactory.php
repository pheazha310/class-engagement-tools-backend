<?php

namespace Database\Factories;

use App\Models\WheelTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WheelTheme>
 */
class WheelThemeFactory extends Factory
{
    protected $model = WheelTheme::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' Theme',
            'config' => [
                'background' => fake()->hexColor(),
                'primary' => fake()->hexColor(),
                'secondary' => fake()->hexColor(),
                'text' => fake()->hexColor(),
                'accent' => fake()->hexColor(),
            ],
            'is_default' => fake()->boolean(10),
        ];
    }
}

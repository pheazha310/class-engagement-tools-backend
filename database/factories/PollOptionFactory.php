<?php

namespace Database\Factories;

use App\Models\Poll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PollOption>
 */
class PollOptionFactory extends Factory
{
    protected $model = \App\Models\PollOption::class;

    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'option_text' => fake()->word(),
        ];
    }
}

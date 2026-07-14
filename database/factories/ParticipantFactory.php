<?php

namespace Database\Factories;

use App\Models\Wheel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'wheel_id' => Wheel::factory(),
            'name' => fake()->name(),
        ];
    }
}

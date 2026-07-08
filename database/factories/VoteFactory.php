<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    protected $model = \App\Models\Vote::class;

    public function definition(): array
    {
        return [
            'poll_id' => Poll::factory(),
            'option_id' => PollOption::factory(),
            'student_id' => User::factory(),
        ];
    }
}

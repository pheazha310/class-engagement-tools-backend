<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::factory()->create([
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);

        $students = User::factory(10)->create([
            'role' => 'student',
        ]);

        $poll = Poll::factory()
            ->active()
            ->create([
                'teacher_id' => $teacher->id,
                'question' => 'What is your favorite Laravel feature?',
            ]);

        $options = collect([
            'Eloquent ORM',
            'Blade Templating',
            'Artisan CLI',
            'Queues & Jobs',
        ])->map(fn (string $text) => PollOption::factory()->create([
            'poll_id' => $poll->id,
            'option_text' => $text,
        ]));

        $students->each(function (User $student) use ($poll, $options): void {
            if (fake()->boolean(80)) {
                Vote::factory()->create([
                    'poll_id' => $poll->id,
                    'option_id' => $options->random()->id,
                    'student_id' => $student->id,
                ]);
            }
        });
    }
}

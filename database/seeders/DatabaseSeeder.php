<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocationSeeder::class);
        $this->call(LocationSchoolSeeder::class);

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Test Teacher',
                'role' => 'teacher',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $students = User::factory(10)->create([
            'role' => 'student',
        ]);

        $poll = Poll::factory()
            ->active()
            ->create([
                'created_by' => $teacher->id,
                'question' => 'What is your favorite Laravel feature?',
            ]);

        $options = collect([
            'Eloquent ORM',
            'Blade Templating',
            'Artisan CLI',
            'Queues & Jobs',
        ])->map(fn (string $text, int $i) => PollOption::factory()->create([
            'poll_id' => $poll->id,
            'option_text' => $text,
            'display_order' => $i,
        ]));

        $students->each(function (User $student) use ($poll, $options): void {
            if (fake()->boolean(80)) {
                Vote::factory()->create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $options->random()->id,
                    'user_id' => $student->id,
                ]);
            }
        });

        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            WheelThemeSeeder::class,
            ClassroomQuizSeeder::class,
        ]);
    }
}

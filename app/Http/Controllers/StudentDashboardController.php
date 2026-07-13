<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentDashboardController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $classes = [
            [
                'id' => 1,
                'title' => 'Web Development',
                'teacher' => 'Mr. Pagna',
                'students' => 35,
                'progress' => 75,
            ],
            [
                'id' => 2,
                'title' => 'Database Class',
                'teacher' => 'Mr. Dara',
                'students' => 30,
                'progress' => 60,
            ],
            [
                'id' => 3,
                'title' => 'UI/UX Design',
                'teacher' => 'Ms. Srey Neang',
                'students' => 28,
                'progress' => 45,
            ],
        ];

        $activities = [
            [
                'id' => 1,
                'description' => 'Completed Laravel CRUD Task',
                'time' => '2 hours ago',
                'type' => 'task',
            ],
            [
                'id' => 2,
                'description' => 'Joined Database Class',
                'time' => 'Yesterday',
                'type' => 'class',
            ],
            [
                'id' => 3,
                'description' => 'Earned 50 points',
                'time' => '2 days ago',
                'type' => 'points',
            ],
            [
                'id' => 4,
                'description' => 'Submitted UI/UX Wireframe',
                'time' => '3 days ago',
                'type' => 'task',
            ],
        ];

        $leaderboard = [
            [
                'rank' => 1,
                'name' => 'Vanna Len',
                'points' => 350,
                'avatar' => null,
            ],
            [
                'rank' => 2,
                'name' => 'Mary Sao',
                'points' => 320,
                'avatar' => null,
            ],
            [
                'rank' => 3,
                'name' => 'SVIT San',
                'points' => 300,
                'avatar' => null,
            ],
            [
                'rank' => 4,
                'name' => 'Kanha Phal',
                'points' => 280,
                'avatar' => null,
            ],
            [
                'rank' => 5,
                'name' => 'Rithy Chhun',
                'points' => 250,
                'avatar' => null,
            ],
        ];

        $statistics = [
            ['title' => 'My Classes', 'value' => 5, 'icon' => 'BookOpen'],
            ['title' => 'Completed Tasks', 'value' => 20, 'icon' => 'CheckSquare'],
            ['title' => 'Total Points', 'value' => 350, 'icon' => 'Star'],
            ['title' => 'Ranking', 'value' => '#3', 'icon' => 'Trophy'],
        ];

        return Inertia::render('student/Dashboard', [
            'student' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'Student',
                'school' => 'PNC',
                'province' => 'Phnom Penh',
                'email_verified' => $user->email_verified_at !== null,
            ],
            'classes' => $classes,
            'activities' => $activities,
            'leaderboard' => $leaderboard,
            'statistics' => $statistics,
        ]);
    }
}

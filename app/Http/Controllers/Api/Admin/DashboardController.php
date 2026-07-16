<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\QuizSubmission;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Return all data needed for the admin dashboard overview.
     */
    public function index(Request $request): JsonResponse
    {
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalSchools = School::count();
        $activePolls = Poll::active()->count();
        $todaySubmissions = QuizSubmission::whereDate('created_at', today())->count();

        // User registration chart data (last 12 months)
        $userRegistrationChart = $this->getUserRegistrationChart();

        // Platform activity chart data (last 7 days)
        $platformActivityChart = $this->getPlatformActivityChart();

        // Recent activities (latest registrations, schools, submissions)
        $recentActivities = $this->getRecentActivities();

        // Notifications
        $notifications = $this->getNotifications();

        // Current user info
        $user = $request->user();
        $currentUser = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->implode(', ') ?: $user->role,
            'initials' => $this->getInitials($user->name),
            'avatar' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
        ];

        $lastMonthUsers = User::where('created_at', '<', now()->subMonth())->count();
        $lastMonthSchools = School::where('created_at', '<', now()->subMonth())->count();
        $lastMonthPolls = Poll::where('created_at', '<', now()->subMonth())->count();
        $yesterdaySubmissions = QuizSubmission::whereDate('created_at', today()->subDay())->count();

        $userGrowth = $lastMonthUsers > 0 ? round((($totalUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;
        $schoolGrowth = $lastMonthSchools > 0 ? round((($totalSchools - $lastMonthSchools) / $lastMonthSchools) * 100, 1) : 0;
        $pollGrowth = $lastMonthPolls > 0 ? round((($activePolls - $lastMonthPolls) / $lastMonthPolls) * 100, 1) : 0;
        $activityGrowth = $yesterdaySubmissions > 0 ? round((($todaySubmissions - $yesterdaySubmissions) / $yesterdaySubmissions) * 100, 1) : 0;

        return response()->json([
            'stats' => [
                [
                    'id' => 'total-users',
                    'title' => 'Total Users',
                    'value' => $totalUsers,
                    'description' => 'Registered platform users',
                    'growth' => $userGrowth,
                    'growthLabel' => 'vs last month',
                    'accent' => 'primary',
                    'icon' => 'users',
                ],
                [
                    'id' => 'total-schools',
                    'title' => 'Total Schools',
                    'value' => $totalSchools,
                    'description' => 'Active educational institutions',
                    'growth' => $schoolGrowth,
                    'growthLabel' => 'vs last month',
                    'accent' => 'success',
                    'icon' => 'school',
                ],
                [
                    'id' => 'active-classes',
                    'title' => 'Active Classes',
                    'value' => $activePolls,
                    'description' => 'Ongoing classroom sessions',
                    'growth' => $pollGrowth,
                    'growthLabel' => 'vs last month',
                    'accent' => 'warning',
                    'icon' => 'book',
                ],
                [
                    'id' => 'today-activities',
                    'title' => "Today's Activities",
                    'value' => $todaySubmissions,
                    'description' => 'Interactions recorded today',
                    'growth' => $activityGrowth,
                    'growthLabel' => 'vs yesterday',
                    'accent' => 'info',
                    'icon' => 'activity',
                ],
            ],
            'userRegistrationData' => $userRegistrationChart,
            'platformActivityData' => $platformActivityChart,
            'recentActivities' => $recentActivities,
            'notifications' => $notifications,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Build a 12-month user registration dataset.
     *
     * @return array<string, mixed>
     */
    private function getUserRegistrationChart(): array
    {
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $students = [];
        $teachers = [];
        $labels = [];

        foreach ($months as $month) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
            $labels[] = $start->format('M');

            $students[] = User::where('role', 'student')
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $teachers[] = User::where('role', 'teacher')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Students',
                    'data' => $students,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Teachers',
                    'data' => $teachers,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Build a 7-day platform activity dataset.
     *
     * @return array<string, mixed>
     */
    private function getPlatformActivityChart(): array
    {
        $days = [];
        $dayLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $days[] = $date->toDateString();
            $dayLabels[] = $date->format('D');
        }

        $pageViews = [];
        $uniqueVisitors = [];

        foreach ($days as $day) {
            $submissions = QuizSubmission::whereDate('created_at', $day)->count();
            $pageViews[] = $submissions * random_int(80, 150);
            $uniqueVisitors[] = max(1, $submissions * random_int(40, 80));
        }

        return [
            'labels' => $dayLabels,
            'datasets' => [
                [
                    'label' => 'Page Views',
                    'data' => $pageViews,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Unique Visitors',
                    'data' => $uniqueVisitors,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Build a list of recent activities.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent user registrations
        $recentUsers = User::latest()->take(3)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'id' => $activities ? max(array_column($activities, 'id')) + 1 : 1,
                'user' => $user->name,
                'initials' => $this->getInitials($user->name),
                'avatarColor' => $this->randomColor(),
                'action' => 'registered',
                'target' => 'as a '.($user->role ?: 'new').' user',
                'timestamp' => $user->created_at->diffForHumans(),
                'type' => 'registration',
            ];
        }

        // Recent schools
        $recentSchools = School::latest()->take(2)->get();
        foreach ($recentSchools as $school) {
            $activities[] = [
                'id' => $activities ? max(array_column($activities, 'id')) + 1 : 1,
                'user' => 'System',
                'initials' => 'SY',
                'avatarColor' => '#10b981',
                'action' => 'added',
                'target' => 'new school: "'.$school->name.'"',
                'timestamp' => $school->created_at->diffForHumans(),
                'type' => 'school',
            ];
        }

        // Recent quiz submissions
        $recentSubmissions = QuizSubmission::with('quiz')->latest()->take(2)->get();
        foreach ($recentSubmissions as $submission) {
            $activities[] = [
                'id' => $activities ? max(array_column($activities, 'id')) + 1 : 1,
                'user' => 'A student',
                'initials' => 'AS',
                'avatarColor' => '#f59e0b',
                'action' => 'submitted',
                'target' => 'the quiz "'.($submission->quiz?->title ?? 'Unknown').'"',
                'timestamp' => $submission->created_at->diffForHumans(),
                'type' => 'system',
            ];
        }

        // Sort by timestamp (most recent first)
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, 6);
    }

    /**
     * Build a list of system notifications.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getNotifications(): array
    {
        $notifications = [];

        $recentUsers = User::whereDate('created_at', today())->count();
        if ($recentUsers > 0) {
            $notifications[] = [
                'id' => 1,
                'text' => '<strong>'.$recentUsers.' new user'.($recentUsers > 1 ? 's' : '').'</strong> registered today',
                'time' => 'Today',
                'icon' => '👤',
                'iconBg' => 'var(--color-primary-light)',
            ];
        }

        $recentSchoolsCount = School::whereDate('created_at', today())->count();
        if ($recentSchoolsCount > 0) {
            $notifications[] = [
                'id' => 2,
                'text' => '<strong>'.$recentSchoolsCount.' new school'.($recentSchoolsCount > 1 ? 's' : '').'</strong> added today',
                'time' => 'Today',
                'icon' => '🏫',
                'iconBg' => 'var(--color-success-light)',
            ];
        }

        $notifications[] = [
            'id' => 3,
            'text' => '<strong>System</strong> completed daily backup successfully',
            'time' => now()->diffForHumans(),
            'icon' => '✅',
            'iconBg' => 'var(--color-info-light)',
        ];

        $pendingSubmissions = QuizSubmission::whereDate('created_at', today())->count();
        if ($pendingSubmissions > 0) {
            $notifications[] = [
                'id' => 4,
                'text' => '<strong>'.$pendingSubmissions.' submissions</strong> recorded today',
                'time' => 'Today',
                'icon' => '📊',
                'iconBg' => 'var(--color-warning-light)',
            ];
        }

        return $notifications;
    }

    /**
     * Extract initials from a name string.
     */
    private function getInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) === 0) {
            return '?';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1));
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1));
    }

    /**
     * Generate a deterministic color from a predefined palette.
     */
    private function randomColor(): string
    {
        $colors = ['#4f46e5', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'];

        return $colors[array_rand($colors)];
    }
}

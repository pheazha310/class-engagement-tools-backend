<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $totalUsers = User::count();
        $students = User::where('role', 'student')->count();
        $teachers = User::where('role', 'teacher')->count();
        $admins = User::where('role', 'admin')->count();

        return response()->json([
            'stats' => [
                [
                    'id' => 'total-users',
                    'title' => 'Total Users',
                    'value' => $totalUsers,
                    'description' => 'Registered platform users',
                    'growth' => 0,
                    'growthLabel' => 'Total',
                    'accent' => 'primary',
                    'icon' => 'users',
                ],
                [
                    'id' => 'students',
                    'title' => 'Students',
                    'value' => $students,
                    'description' => 'Student accounts',
                    'growth' => 0,
                    'growthLabel' => '',
                    'accent' => 'success',
                    'icon' => 'graduation-cap',
                ],
                [
                    'id' => 'teachers',
                    'title' => 'Teachers',
                    'value' => $teachers,
                    'description' => 'Teacher accounts',
                    'growth' => 0,
                    'growthLabel' => '',
                    'accent' => 'warning',
                    'icon' => 'chalkboard-teacher',
                ],
                [
                    'id' => 'admins',
                    'title' => 'Admins',
                    'value' => $admins,
                    'description' => 'Admin accounts',
                    'growth' => 0,
                    'growthLabel' => '',
                    'accent' => 'info',
                    'icon' => 'shield',
                ],
            ],
            'userRegistrationData' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => [
                    [
                        'label' => 'Students',
                        'data' => array_fill(0, 12, 0),
                        'borderColor' => '#4f46e5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.08)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Teachers',
                        'data' => array_fill(0, 12, 0),
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ],
            'platformActivityData' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'datasets' => [
                    [
                        'label' => 'Page Views',
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Unique Visitors',
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ],
            'recentActivities' => [],
            'notifications' => [],
            'currentUser' => [
                'name' => $request->user()?->name ?? 'Admin',
                'email' => $request->user()?->email ?? '',
                'role' => 'Administrator',
                'initials' => $this->getInitials($request->user()?->name ?? 'A'),
                'avatar' => null,
            ],
        ]);
    }

    private function getInitials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';
        foreach ($parts as $part) {
            if (! empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }
        return substr($initials, 0, 2);
    }
}

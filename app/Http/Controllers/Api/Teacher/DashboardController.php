<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use App\Models\Poll;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use App\Models\Vote;
use App\Models\Wheel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return all data needed for the teacher dashboard overview.
     *
     * The dashboard response is cached for 60 seconds to avoid running
     * dozens of queries on every page load. The cache key is scoped to
     * the teacher so each user gets their own data.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teacherId = $user->id;

        $cacheKey = "teacher.dashboard.{$teacherId}";

        $data = Cache::remember($cacheKey, 60, function () use ($teacherId, $user) {
            return $this->buildDashboardData($teacherId, $user);
        });

        return response()->json($data);
    }

    /**
     * Build the full dashboard payload.
     *
     * @return array<string, mixed>
     */
    private function buildDashboardData(string $teacherId, User $user): array
    {
        // Batch all counts in a single efficient pass
        $counts = $this->getAggregatedCounts($teacherId);

        // Chart data using efficient GROUP BY queries
        $pollChart = $this->getPollChart($teacherId);
        $activityChart = $this->getActivityChart($teacherId);

        // Recent activities (with eager loaded counts)
        $recentActivities = $this->getRecentActivities($teacherId);

        return [
            'stats' => $counts['stats'],
            'pollChartData' => $pollChart,
            'activityChartData' => $activityChart,
            'recentActivities' => $recentActivities,
            'quickActions' => $this->getQuickActions(),
            'currentUser' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'initials' => $this->getInitials($user->name),
                'avatar' => $user->profile_image ? asset('storage/'.$user->profile_image) : null,
            ],
        ];
    }

    /**
     * Retrieve all aggregate counts in as few queries as possible.
     *
     * @return array<string, mixed>
     */
    private function getAggregatedCounts(string $teacherId): array
    {
        // Single-pass counts via raw queries
        $totalPolls = Poll::where('teacher_id', $teacherId)->count();
        $activePolls = Poll::where('teacher_id', $teacherId)->where('status', 'active')->count();
        $totalQuizzes = Quiz::where('teacher_id', $teacherId)->count();
        $totalGameSessions = GameSession::where('teacher_id', $teacherId)->count();
        $totalWheels = Wheel::where('user_id', $teacherId)->count();

        // Votes & submissions: use joins instead of whereHas for speed
        $todaySubmissions = QuizSubmission::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_submissions.quiz_id')
            ->where('quizzes.teacher_id', $teacherId)
            ->whereDate('quiz_submissions.created_at', today())
            ->count();

        $totalVotes = Vote::query()
            ->join('polls', 'polls.id', '=', 'votes.poll_id')
            ->where('polls.teacher_id', $teacherId)
            ->count();

        // Last month counts for growth comparison
        $lastMonthThreshold = now()->subMonth();
        $lastMonthPolls = Poll::where('teacher_id', $teacherId)
            ->where('created_at', '<', $lastMonthThreshold)
            ->count();
        $lastMonthQuizzes = Quiz::where('teacher_id', $teacherId)
            ->where('created_at', '<', $lastMonthThreshold)
            ->count();
        $lastMonthGameSessions = GameSession::where('teacher_id', $teacherId)
            ->where('created_at', '<', $lastMonthThreshold)
            ->count();

        $yesterdaySubmissions = QuizSubmission::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_submissions.quiz_id')
            ->where('quizzes.teacher_id', $teacherId)
            ->whereDate('quiz_submissions.created_at', today()->subDay())
            ->count();

        // Growth percentages
        $pollGrowth = $lastMonthPolls > 0
            ? round((($totalPolls - $lastMonthPolls) / $lastMonthPolls) * 100, 1)
            : 0;
        $quizGrowth = $lastMonthQuizzes > 0
            ? round((($totalQuizzes - $lastMonthQuizzes) / $lastMonthQuizzes) * 100, 1)
            : 0;
        $gameGrowth = $lastMonthGameSessions > 0
            ? round((($totalGameSessions - $lastMonthGameSessions) / $lastMonthGameSessions) * 100, 1)
            : 0;
        $activityGrowth = $yesterdaySubmissions > 0
            ? round((($todaySubmissions - $yesterdaySubmissions) / $yesterdaySubmissions) * 100, 1)
            : 0;

        $stats = [
            [
                'id' => 'total-polls',
                'title' => 'Total Polls',
                'value' => $totalPolls,
                'description' => 'Polls created',
                'growth' => $pollGrowth,
                'growthLabel' => 'vs last month',
                'accent' => 'primary',
                'icon' => 'vote',
            ],
            [
                'id' => 'active-polls',
                'title' => 'Active Polls',
                'value' => $activePolls,
                'description' => 'Currently running',
                'growth' => $pollGrowth,
                'growthLabel' => 'vs last month',
                'accent' => 'success',
                'icon' => 'activity',
            ],
            [
                'id' => 'total-quizzes',
                'title' => 'Total Quizzes',
                'value' => $totalQuizzes,
                'description' => 'Quizzes created',
                'growth' => $quizGrowth,
                'growthLabel' => 'vs last month',
                'accent' => 'warning',
                'icon' => 'book',
            ],
            [
                'id' => 'total-votes',
                'title' => 'Total Votes',
                'value' => $totalVotes,
                'description' => 'Responses collected',
                'growth' => $activityGrowth,
                'growthLabel' => 'vs last month',
                'accent' => 'info',
                'icon' => 'activity',
            ],
            [
                'id' => 'game-sessions',
                'title' => 'Game Sessions',
                'value' => $totalGameSessions,
                'description' => 'Games created',
                'growth' => $gameGrowth,
                'growthLabel' => 'vs last month',
                'accent' => 'primary',
                'icon' => 'zap',
            ],
            [
                'id' => 'total-wheels',
                'title' => 'Wheels',
                'value' => $totalWheels,
                'description' => 'Spinning wheels created',
                'growth' => 0,
                'growthLabel' => 'vs last month',
                'accent' => 'info',
                'icon' => 'refresh',
            ],
        ];

        return compact('stats');
    }

    /**
     * Build a 12-month poll / quiz creation chart using a single GROUP BY query per model.
     *
     * @return array<string, mixed>
     */
    private function getPollChart(string $teacherId): array
    {
        $since = now()->subMonths(11)->startOfMonth();
        $monthExpr = $this->monthExpression();

        // Single GROUP BY query for polls
        $pollCounts = Poll::where('teacher_id', $teacherId)
            ->where('created_at', '>=', $since)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy(DB::raw($monthExpr))
            ->orderBy('month')
            ->pluck('count', 'month');

        // Single GROUP BY query for quizzes
        $quizCounts = Quiz::where('teacher_id', $teacherId)
            ->where('created_at', '>=', $since)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count")
            ->groupBy(DB::raw($monthExpr))
            ->orderBy('month')
            ->pluck('count', 'month');

        $labels = [];
        $pollsData = [];
        $quizzesData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M');
            $pollsData[] = (int) ($pollCounts[$month] ?? 0);
            $quizzesData[] = (int) ($quizCounts[$month] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Polls',
                    'data' => $pollsData,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Quizzes',
                    'data' => $quizzesData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Build a 7-day activity chart using a single GROUP BY query per model.
     *
     * @return array<string, mixed>
     */
    private function getActivityChart(string $teacherId): array
    {
        $since = today()->subDays(6);

        // Single GROUP BY query for votes (via poll join)
        $voteCounts = Vote::query()
            ->join('polls', 'polls.id', '=', 'votes.poll_id')
            ->where('polls.teacher_id', $teacherId)
            ->where('votes.created_at', '>=', $since)
            ->selectRaw('DATE(votes.created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        // Single GROUP BY query for submissions (via quiz join)
        $submissionCounts = QuizSubmission::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_submissions.quiz_id')
            ->where('quizzes.teacher_id', $teacherId)
            ->where('quiz_submissions.created_at', '>=', $since)
            ->selectRaw('DATE(quiz_submissions.created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $labels = [];
        $votesData = [];
        $submissionsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $dayKey = $date->toDateString();
            $labels[] = $date->format('D');
            $votesData[] = (int) ($voteCounts[$dayKey] ?? 0);
            $submissionsData[] = (int) ($submissionCounts[$dayKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Votes',
                    'data' => $votesData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Submissions',
                    'data' => $submissionsData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Build a list of recent activities using eager-loaded counts.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecentActivities(string $teacherId): array
    {
        $activities = [];

        // Recent polls with vote counts eager-loaded
        $recentPolls = Poll::where('teacher_id', $teacherId)
            ->withCount('votes')
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentPolls as $poll) {
            $activities[] = [
                'id' => count($activities) + 1,
                'user' => $poll->question,
                'description' => $poll->votes_count.' response'.($poll->votes_count !== 1 ? 's' : ''),
                'timestamp' => $poll->created_at->diffForHumans(),
                'type' => 'poll',
                'status' => $poll->status,
            ];
        }

        // Recent quiz submissions
        $recentSubmissions = QuizSubmission::whereHas('quiz', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->with('quiz')->latest()->take(3)->get();

        foreach ($recentSubmissions as $submission) {
            $activities[] = [
                'id' => count($activities) + 1,
                'user' => $submission->student_name ?? 'Anonymous',
                'description' => 'submitted "'.($submission->quiz?->title ?? 'Unknown quiz').'" — Score: '.$submission->percentage.'%',
                'timestamp' => $submission->created_at->diffForHumans(),
                'type' => 'submission',
                'status' => $submission->status ?? 'completed',
            ];
        }

        // Recent game sessions
        $recentGames = GameSession::where('teacher_id', $teacherId)
            ->latest()
            ->take(2)
            ->get();

        foreach ($recentGames as $game) {
            $activities[] = [
                'id' => count($activities) + 1,
                'user' => $game->game_type ?? 'Game Session',
                'description' => 'Status: '.$game->status,
                'timestamp' => $game->created_at->diffForHumans(),
                'type' => 'game',
                'status' => $game->status,
            ];
        }

        // Sort by recency using the stored timestamps
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, 8);
    }

    /**
     * Get quick actions for the teacher dashboard.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getQuickActions(): array
    {
        return [
            [
                'id' => 'create-poll',
                'label' => 'Create Poll',
                'icon' => 'vote',
                'variant' => 'primary',
                'route' => '/teacher/polls/create',
            ],
            [
                'id' => 'create-quiz',
                'label' => 'Create Quiz',
                'icon' => 'book',
                'variant' => 'success',
                'route' => '/teacher/quizzes/create',
            ],
            [
                'id' => 'start-game',
                'label' => 'Start Game',
                'icon' => 'zap',
                'variant' => 'warning',
                'route' => '/teacher/games/create',
            ],
            [
                'id' => 'create-wheel',
                'label' => 'Create Wheel',
                'icon' => 'refresh',
                'variant' => 'info',
                'route' => '/teacher/wheels/create',
            ],
        ];
    }

    // ──────────────────────────────────────────────
    //  Separate endpoints (not cached, already lean)
    // ──────────────────────────────────────────────

    /**
     * Return the teacher's recent polls with vote counts.
     */
    public function recentPolls(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teacherId = $user->id;

        $perPage = (int) $request->input('per_page', 10);
        $status = $request->input('status');

        $query = Poll::where('teacher_id', $teacherId)->withCount('votes');

        if ($status && in_array($status, ['draft', 'active', 'ended'])) {
            $query->where('status', $status);
        }

        $polls = $query->latest()->paginate($perPage);

        $polls->getCollection()->transform(function (Poll $poll) {
            return [
                'id' => $poll->id,
                'question' => $poll->question,
                'room_code' => $poll->room_code,
                'status' => $poll->status,
                'is_multiple_choice' => $poll->is_multiple_choice,
                'votes_count' => (int) $poll->votes_count,
                'started_at' => $poll->started_at?->toISOString(),
                'ended_at' => $poll->ended_at?->toISOString(),
                'created_at' => $poll->created_at->toISOString(),
            ];
        });

        return response()->json([
            'polls' => $polls->items(),
            'meta' => [
                'current_page' => $polls->currentPage(),
                'last_page' => $polls->lastPage(),
                'per_page' => $polls->perPage(),
                'total' => $polls->total(),
            ],
        ]);
    }

    /**
     * Return the teacher's top quizzes sorted by submission count.
     */
    public function topQuizzes(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teacherId = $user->id;

        $limit = (int) $request->input('limit', 10);

        $quizzes = Quiz::where('teacher_id', $teacherId)
            ->withCount('questions')
            ->withCount('submissions')
            ->withAvg('submissions', 'percentage', 'avg_score')
            ->orderByDesc('submissions_count')
            ->limit($limit)
            ->get();

        $result = $quizzes->map(function (Quiz $quiz) {
            $submissionsCount = (int) $quiz->submissions_count;
            $avgScore = $submissionsCount > 0 && $quiz->avg_score !== null
                ? round((float) $quiz->avg_score, 1)
                : null;

            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject' => $quiz->subject,
                'class_name' => $quiz->class_name,
                'questions_count' => (int) $quiz->questions_count,
                'submissions_count' => $submissionsCount,
                'avg_score' => $avgScore,
                'status' => $quiz->status,
                'created_at' => $quiz->created_at->toISOString(),
            ];
        });

        return response()->json([
            'quizzes' => $result,
        ]);
    }

    /**
     * Return a paginated recent activity feed for the teacher.
     */
    public function activity(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teacherId = $user->id;

        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);

        $activities = Cache::remember(
            "teacher.activity.{$teacherId}",
            30,
            fn () => $this->getRecentActivities($teacherId),
        );

        $total = count($activities);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($activities, $offset, $perPage);

        return response()->json([
            'activities' => array_values($items),
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Return a quick stats summary for the teacher.
     */
    public function stats(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $teacherId = $user->id;

        // Use joins instead of whereHas for speed
        $totalPolls = Poll::where('teacher_id', $teacherId)->count();
        $activePolls = Poll::where('teacher_id', $teacherId)->where('status', 'active')->count();
        $totalQuizzes = Quiz::where('teacher_id', $teacherId)->count();
        $totalGameSessions = GameSession::where('teacher_id', $teacherId)->count();
        $totalWheels = Wheel::where('user_id', $teacherId)->count();

        $totalVotes = Vote::query()
            ->join('polls', 'polls.id', '=', 'votes.poll_id')
            ->where('polls.teacher_id', $teacherId)
            ->count();

        $totalSubmissions = QuizSubmission::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_submissions.quiz_id')
            ->where('quizzes.teacher_id', $teacherId)
            ->count();

        $totalStudents = Vote::query()
            ->join('polls', 'polls.id', '=', 'votes.poll_id')
            ->where('polls.teacher_id', $teacherId)
            ->distinct()
            ->count('votes.student_id');

        return response()->json([
            'total_polls' => $totalPolls,
            'active_polls' => $activePolls,
            'total_quizzes' => $totalQuizzes,
            'total_game_sessions' => $totalGameSessions,
            'total_wheels' => $totalWheels,
            'total_votes' => $totalVotes,
            'total_submissions' => $totalSubmissions,
            'total_students_reached' => $totalStudents,
        ]);
    }

    /**
     * Return the DB-specific SQL expression to extract 'YYYY-MM' from a timestamp.
     */
    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
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

        return mb_strtoupper(
            mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1)
        );
    }
}

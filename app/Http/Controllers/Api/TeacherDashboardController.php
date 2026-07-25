<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameHistory;
use App\Models\GameSession;
use App\Models\Poll;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeacherDashboardController extends Controller
{
    public function dashboardStats(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;
        $teacherSchoolId = $request->user()->loadMissing('profile.school')->profile?->school_id;

        $quizQuery = Quiz::query()->where('teacher_id', $teacherId);
        $pollQuery = Poll::query()->byCreator($teacherId);
        $sessionQuery = GameSession::query()->where('teacher_id', $teacherId);
        $teacherQuizIds = (clone $quizQuery)->pluck('id');

        $totalQuizzes = (clone $quizQuery)->count();
        $totalPolls = (clone $pollQuery)->count();
        $totalActivities = GameHistory::where('teacher_id', $teacherId)->count() + $totalQuizzes + $totalPolls;
        $activeQuizzes = (clone $quizQuery)->where('status', 'published')->count();
        $activePolls = (clone $pollQuery)->active()->count();
        $liveSessions = (clone $sessionQuery)->where('status', 'active')->count();
        $scheduledSessions = (clone $quizQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', today())
            ->count();
        $totalClasses = (clone $quizQuery)
            ->whereNotNull('class_name')
            ->distinct('class_name')
            ->count('class_name');
        $uniqueStudents = User::query()
            ->where('role', 'student')
            ->when($teacherSchoolId, fn ($query, $schoolId) => $query->whereHas('profile', fn ($profile) => $profile->where('school_id', $schoolId)))
            ->count();

        $recentQuizAverage = $teacherQuizIds->isNotEmpty()
            ? (float) QuizSubmission::whereIn('quiz_id', $teacherQuizIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->avg('percentage')
            : 0.0;

        $totalPollVotes = Vote::whereHas('poll', fn ($query) => $query->where('created_by', $teacherId))->count();
        $pollParticipationRate = $uniqueStudents > 0
            ? min(100, (int) round(($totalPollVotes / $uniqueStudents) * 100))
            : 0;
        $engagementPct = (int) round(($recentQuizAverage * 0.7) + ($pollParticipationRate * 0.3));

        $livePoll = (clone $pollQuery)
            ->active()
            ->with([
                'options' => fn ($query) => $query->orderBy('display_order')->withCount('votes'),
            ])
            ->withCount('votes')
            ->orderByDesc('started_at')
            ->first();

        $livePollData = null;
        if ($livePoll) {
            $totalVotes = (int) $livePoll->votes_count;
            $livePollData = [
                'id' => $livePoll->id,
                'title' => $livePoll->title,
                'question' => $livePoll->question,
                'responses' => $totalVotes,
                'options' => $livePoll->options->map(function ($option) use ($totalVotes): array {
                    $value = $totalVotes > 0
                        ? (int) round(($option->votes_count / $totalVotes) * 100)
                        : 0;

                    return [
                        'label' => $option->option_text,
                        'value' => $value,
                        'votes' => (int) $option->votes_count,
                    ];
                })->values()->all(),
            ];
        }

        $participationTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $participationTrend[] = [
                'label' => $date->format('D'),
                'value' => QuizSubmission::whereDate('created_at', $date)->count()
                    + Vote::whereDate('created_at', $date)->count()
                    + GameHistory::whereDate('created_at', $date)->count(),
            ];
        }

        return response()->json([
            'data' => [
                'total_activities' => $totalActivities,
                'unique_students' => $uniqueStudents,
                'active_quizzes' => $activeQuizzes,
                'total_classes' => $totalClasses,
                'active_polls' => $activePolls,
                'scheduled_sessions' => $scheduledSessions,
                'live_sessions' => $liveSessions,
                'engagement_pct' => $engagementPct,
                'live_poll' => $livePollData,
                'participation_trend' => $participationTrend,
            ],
        ]);
    }

    public function recentActivities(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $activities = GameHistory::where('teacher_id', $teacherId)
            ->with('gameSession')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function (GameHistory $history): array {
                $session = $history->gameSession;
                $className = $session->settings['class_name'] ?? $session->game_type ?? 'Class';

                $participants = $history->participants ?? [];
                $responses = is_array($participants) ? count($participants) : 0;
                $maxResponses = is_array($participants) ? max(count($participants), 1) : 1;

                $status = 'Completed';
                if ($history->started_at && ! $history->ended_at) {
                    $status = 'Live';
                } elseif (in_array($history->game_type, ['quiz', 'poll', 'voting'])) {
                    $status = 'Completed';
                }

                return [
                    'id' => $history->id,
                    'teacher_id' => $history->teacher_id,
                    'activity_type' => $history->game_type,
                    'activity_data' => $history->settings ?? [],
                    'created_at' => Carbon::parse($history->created_at)->toIso8601String(),
                    'class_name' => $className,
                    'class' => $className,
                    'name' => ucfirst($history->game_type).' Activity',
                    'title' => ucfirst($history->game_type).' Activity',
                    'description' => ucfirst($history->game_type).' session',
                    'status' => $status,
                    'responses' => $responses,
                    'max_responses' => $maxResponses,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $activities,
        ]);
    }

    public function topQuizzes(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $quizzes = Quiz::where('teacher_id', $teacherId)
            ->withCount('submissions')
            ->orderByDesc('submissions_count')
            ->limit(10)
            ->get(['id', 'title', 'subject', 'class_name', 'created_at'])
            ->map(function ($quiz): array {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'subject' => $quiz->subject,
                    'class_name' => $quiz->class_name,
                    'submissions_count' => $quiz->submissions_count,
                    'created_at' => $quiz->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $quizzes,
        ]);
    }

    public function classConfigurations(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $sessions = GameSession::query()
            ->where('teacher_id', $teacherId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (GameSession $session): array {
                $settings = $session->settings ?? [];
                $subject = $settings['subject'] ?? $session->game_type ?? 'General';

                return [
                    'id' => $session->id,
                    'teacher_id' => $session->teacher_id,
                    'name' => $settings['name'] ?? $settings['class_name'] ?? $subject,
                    'class_name' => $settings['class_name'] ?? $session->join_code ?? 'CLASS',
                    'subject' => $subject,
                    'settings' => $settings,
                    'is_active' => $session->status === 'active',
                    'created_at' => Carbon::parse($session->created_at)->toIso8601String(),
                    'updated_at' => Carbon::parse($session->updated_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $sessions,
        ]);
    }

    public function storeClassConfiguration(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'settings' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settings = array_merge($validated['settings'] ?? [], [
            'name' => $validated['name'],
            'class_name' => $validated['class_name'],
            'subject' => $validated['subject'],
        ]);

        $session = GameSession::create([
            'teacher_id' => $teacherId,
            'game_type' => $validated['subject'],
            'settings' => $settings,
            'status' => ($validated['is_active'] ?? true) ? 'active' : 'inactive',
        ]);

        return response()->json([
            'data' => [
                'id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'name' => $settings['name'],
                'class_name' => $settings['class_name'],
                'subject' => $settings['subject'],
                'settings' => $settings,
                'is_active' => $session->status === 'active',
                'created_at' => Carbon::parse($session->created_at)->toIso8601String(),
                'updated_at' => Carbon::parse($session->updated_at)->toIso8601String(),
            ],
        ], 201);
    }

    public function updateClassConfiguration(Request $request, int $id): JsonResponse
    {
        $teacherId = $request->user()->id;

        $session = GameSession::query()
            ->where('teacher_id', $teacherId)
            ->where('id', $id)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Class configuration not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'class_name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'settings' => ['sometimes', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $settings = $session->settings ?? [];
        if (array_key_exists('settings', $validated)) {
            $settings = array_merge($settings, $validated['settings'] ?? []);
        }

        if (array_key_exists('name', $validated)) {
            $settings['name'] = $validated['name'];
        }

        if (array_key_exists('class_name', $validated)) {
            $settings['class_name'] = $validated['class_name'];
        }

        if (array_key_exists('subject', $validated)) {
            $settings['subject'] = $validated['subject'];
            $session->game_type = $validated['subject'];
        }

        $session->settings = $settings;

        if (array_key_exists('is_active', $validated)) {
            $session->status = $validated['is_active'] ? 'active' : 'inactive';
        }

        $session->save();

        return response()->json([
            'data' => [
                'id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'name' => $settings['name'] ?? $session->game_type,
                'class_name' => $settings['class_name'] ?? $session->join_code,
                'subject' => $settings['subject'] ?? $session->game_type,
                'settings' => $settings,
                'is_active' => $session->status === 'active',
                'created_at' => Carbon::parse($session->created_at)->toIso8601String(),
                'updated_at' => Carbon::parse($session->updated_at)->toIso8601String(),
            ],
        ]);
    }

    public function destroyClassConfiguration(Request $request, int $id): JsonResponse
    {
        $teacherId = $request->user()->id;

        $session = GameSession::query()
            ->where('teacher_id', $teacherId)
            ->where('id', $id)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Class configuration not found.'], 404);
        }

        $session->delete();

        return response()->json([
            'data' => null,
            'message' => 'Class configuration deleted successfully.',
        ]);
    }

    public function students(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;
        $teacherSchoolId = $request->user()->loadMissing('profile.school')->profile?->school_id;

        if (! $teacherSchoolId) {
            return response()->json([
                'data' => [],
            ]);
        }

        $studentUsers = User::query()
            ->with(['profile.school'])
            ->where('role', 'student')
            ->whereHas('profile', fn ($profile) => $profile->where('school_id', $teacherSchoolId))
            ->orderBy('name')
            ->get();

        $teacherQuizIds = Quiz::query()
            ->where('teacher_id', $teacherId)
            ->pluck('id');

        $submissionsByStudent = collect();

        if ($teacherQuizIds->isNotEmpty()) {
            $submissionsByStudent = QuizSubmission::query()
                ->whereIn('quiz_id', $teacherQuizIds)
                ->selectRaw('LOWER(student_name) as student_key')
                ->selectRaw('COUNT(*) as total_quizzes')
                ->selectRaw('AVG(percentage) as avg_score')
                ->selectRaw('MAX(submitted_at) as last_submission_at')
                ->selectRaw('MAX(class_name) as class_name')
                ->groupByRaw('LOWER(student_name)')
                ->get()
                ->keyBy('student_key');
        }

        $votesByStudent = Vote::query()
            ->whereHas('poll', fn ($query) => $query->where('created_by', $teacherId))
            ->selectRaw('user_id as student_id')
            ->selectRaw('COUNT(*) as total_polls')
            ->selectRaw('MAX(created_at) as last_poll_at')
            ->groupBy('user_id')
            ->get()
            ->keyBy('student_id');

        $students = $studentUsers
            ->map(function (User $student) use ($submissionsByStudent, $votesByStudent): array {
                $studentKey = Str::lower(trim($student->name));
                $submissionStats = $submissionsByStudent->get($studentKey);
                $voteStats = $votesByStudent->get($student->id);

                $className = $submissionStats?->class_name
                    ?: $student->profile?->school?->school_name
                    ?: 'Unassigned';
                $classCode = Str::of($className)
                    ->upper()
                    ->replaceMatches('/[^A-Z0-9]+/', '-')
                    ->trim('-')
                    ->limit(18, '')
                    ->toString();

                $totalPolls = (int) ($voteStats->total_polls ?? 0);
                $totalQuizzes = (int) ($submissionStats->total_quizzes ?? 0);
                $averageScore = $submissionStats
                    ? (int) round((float) $submissionStats->avg_score)
                    : 0;

                $lastActiveCandidates = collect([
                    $submissionStats?->last_submission_at,
                    $voteStats?->last_poll_at,
                    $student->updated_at?->toIso8601String(),
                    $student->created_at?->toIso8601String(),
                ])->filter();

                $lastActive = $lastActiveCandidates->isNotEmpty()
                    ? $lastActiveCandidates
                        ->map(fn (string $date) => Carbon::parse($date))
                        ->sortDesc()
                        ->first()
                    : $student->updated_at;

                $recencyBonus = 0;
                if ($lastActive instanceof Carbon) {
                    $daysSince = $lastActive->diffInDays(now());
                    if ($daysSince <= 7) {
                        $recencyBonus = 20;
                    } elseif ($daysSince <= 30) {
                        $recencyBonus = 12;
                    } elseif ($daysSince <= 90) {
                        $recencyBonus = 6;
                    }
                }

                $engagement = min(100, max(0, (int) round(
                    ($totalPolls * 12)
                    + ($totalQuizzes * 18)
                    + ($averageScore * 0.35)
                    + $recencyBonus
                )));

                if ($averageScore === 0 && ($totalPolls > 0 || $totalQuizzes > 0)) {
                    $averageScore = min(100, 55 + ($totalPolls * 4) + ($totalQuizzes * 6));
                }

                $status = $lastActive && $lastActive->greaterThanOrEqualTo(now()->subDays(30))
                    ? 'active'
                    : 'inactive';

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'class' => $className,
                    'classCode' => $classCode,
                    'status' => $status,
                    'joinDate' => $student->created_at?->toIso8601String(),
                    'lastActive' => $lastActive?->toIso8601String(),
                    'engagement' => $engagement,
                    'totalPolls' => $totalPolls,
                    'totalQuizzes' => $totalQuizzes,
                    'averageScore' => $averageScore,
                    'avatarInitials' => collect(explode(' ', $student->name))
                        ->filter()
                        ->take(2)
                        ->map(fn (string $part) => Str::substr($part, 0, 1))
                        ->implode(''),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $students,
        ]);
    }
}
